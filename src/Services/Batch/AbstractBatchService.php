<?php

namespace Etsy\Services\Batch;

use Etsy\Helper\ImageHelper;
use Etsy\Helper\SettingsHelper;
use Etsy\Services\Item\DeleteListingService;
use Illuminate\Support\Collection;
use Plenty\Modules\Catalog\Contracts\CatalogExportRepositoryContract;
use Plenty\Modules\Catalog\Contracts\CatalogExportServiceContract;
use Plenty\Modules\Catalog\Contracts\CatalogRepositoryContract;
use Plenty\Modules\Catalog\Contracts\TemplateContainerContract;
use Plenty\Modules\Catalog\Templates\Template;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Modules\Pim\SearchService\Filter\VariationFunctionbeatTimestampFilter;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Context\GroupBy;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;
use Plenty\Repositories\Models\PaginatedResult;

/**
 * Class AbstractBatchService
 */
abstract class AbstractBatchService
{

    /**
     * template uuid
     */
    const TEMPLATE = '53237d32-45f0-3aa6-ab69-5d3617004846';

    /**
     * Fields that should be returned by the catalog, even though they don't get mapped
     */
    const ADDITIONAL_FIELDS = [
        'itemId' => 'item.id',
        'variationId' => 'variation.id',
        'isActive' => 'variation.isActive',
        'isMain' => 'variation.isMain',
//        'stockLimitation' => 'variation.stockLimitation',
        'texts' => 'texts',
        'defaultCategories' => 'defaultCategories',
        'attributes' => 'attributes',
        'images' => 'images',
        'skus' => 'skus'
    ];

    /**
     * @var CatalogExportServiceContract
     */
    protected $catalogExportService;

    /**
     * @var SettingsHelper $settingshelper
     */
    protected $settingshelper;

    /**
     * @var Template
     */
    protected $template;

    /**
     * AbstractBatchService constructor.
     * @param TemplateContainerContract $templateContainer
     * @throws \Exception
     */
    public function __construct(TemplateContainerContract $templateContainer)
    {
        $this->settingshelper = pluginApp(SettingsHelper::class);

        /** @var CatalogExportRepositoryContract $catalogExportRepository */
        $catalogExportRepository = pluginApp(CatalogExportRepositoryContract::class);
        /** @var CatalogRepositoryContract $catalogRepository */
        $catalogRepository = pluginApp(CatalogRepositoryContract::class);
        $catalogRepository->setFilters([
            'template' => self::TEMPLATE,
            'active' => true
        ]);
        $id = null;
        /** @var Collection $mappings */
        $catalogs = $catalogRepository->all()->getResult();

        if (count($catalogs) > 1) {
            // Currently due to lack of filtering and the low API limit we can not export multiple catalogs.
            // Since we can't know which catalog the customer wants to export we abort at this point
            // if multiple active catalogs with the etsy template exist
            throw new \Exception('Multiple active catalogs set.');
        }

        foreach ($catalogs as $catalog) {
            $id = $catalog->id;
            $templateId = $catalog->template;
            $this->template = $templateContainer->getTemplate($templateId);
            break;
        }

        $this->catalogExportService = $catalogExportRepository->exportById($id);
        $this->catalogExportService->setSettings(['marketId' => $this->settingshelper->get($this->settingshelper::SETTINGS_ORDER_REFERRER)]);
        $this->catalogExportService->setAdditionalFields(self::ADDITIONAL_FIELDS);
    }

    /**
     * Run the batch service.
     */
    final public function run($lastRun = null)
    {
        $this->deleteDeprecatedListing();

        if ($lastRun) {
            //$this->catalogExportService->setUpdatedSince($lastRun);
        }

        /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);

        $context = new VariationDataInterfaceContext();
        $context->addPart(pluginApp(VariationBaseAttribute::class));
        $context->setGetAllFound(true);

        /** @var  $result */
        $result = $vdi->getResult($context);

        $itemIds = [];

        try {
            foreach($result->get() as $variation) {
                if($variation->timestamps->related->greaterThanOrEqualTo($lastRun)
                    || $variation->timestamps->base->greaterThanOrEqualTo($lastRun)) {
                    $itemIds[] = $variation->base->itemId;
                }
            }
        } catch (\Exception $exception) {
            //Todo log
        }

        try {
            $this->template->addFilter([
                'name' => 'item.hasIds',
                'params' => [
                    [
                        'name' => 'itemIds',
                        'value' => $itemIds
                    ]
                ]
            ]);
        } catch (\Exception $exception) {
            //todo log
        }

        $result = $this->catalogExportService->getResult();

        foreach ($result as $page) {
            $this->export($page);
        }
    }


    /**
     * Delete listings on Etsy and the entry in the market status table if the variation was deleted.
     */
    private function deleteDeprecatedListing()
    {
        /** @var ImageHelper $imageHelper */
        $imageHelper = pluginApp(ImageHelper::class);
        $deleteListingService = pluginApp(DeleteListingService::class);
        /** @var VariationRepositoryContract $variationRepo */

        $filter = [
            'marketId' => $this->settingshelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER)
        ];

        /** @var VariationSkuRepositoryContract $variationSkuRepository */
        $variationSkuRepository = pluginApp(VariationSkuRepositoryContract::class);

        $variationSkuList = $variationSkuRepository->search($filter);

        //each listings id linked to true or false -> true means that the listing has active variations
        $listings = [];

        /** @var VariationSku $variationSku */
        foreach ($variationSkuList as $key => $variationSku) {
            if (!isset($listings[$variationSku->parentSku])) {
                $listings[$variationSku->parentSku] = false;
            }

            if (!isset($variationSku->deletedAt)) {
                $listings[$variationSku->parentSku] = true;
                continue;
            }

            $variationSkuRepository->delete((int)$variationSku->id);
            unset($variationSkuList[$key]);
        }

        foreach ($listings as $listingId => $hasVariations) {
            if (!$hasVariations) {
                $imageHelper->delete($listingId);
                $deleteListingService->delete($listingId);
            }
        }
    }

    /**
     * Execute the export process.
     *
     * @param array $catalogResult
     * @return mixed
     */
    protected abstract function export(array $catalogResult);
}