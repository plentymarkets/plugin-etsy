<?php

namespace Etsy\Services\Batch;

use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Catalog\Contracts\CatalogExportRepositoryContract;
use Plenty\Modules\Catalog\Contracts\CatalogExportServiceContract;
use Plenty\Modules\Catalog\Contracts\CatalogRepositoryContract;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\System\Contracts\WebstoreConfigurationRepositoryContract;

/**
 * Class AbstractBatchService
 */
abstract class AbstractBatchService
{
    /**
     * template uuid
     */
    const TEMPLATE = 'c96973ee-0b1a-3ce0-b7a8-38e45bea658e';

    /**
     * Fields that should be returned by the catalog, even though they don't get mapped
     */
    const ADDITIONAL_FIELDS = [
        'isActive' => 'variation.isActive',
        'isMain' => 'variation.isMain',
        'texts' => 'texts',
        'salesPrices' => 'salesPrices', //todo: Entfernen sobald es settings für Katalog gibt
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
     * @param VariationElasticSearchScrollRepositoryContract $variationElasticSearchScrollRepository
     */
    public function __construct(VariationElasticSearchScrollRepositoryContract $variationElasticSearchScrollRepository)
    {
        $this->settingshelper = pluginApp(SettingsHelper::class);

        /** @var CatalogExportRepositoryContract $catalogExportRepository */
        $catalogExportRepository = pluginApp(CatalogExportRepositoryContract::class);
        $catalogRepository = pluginApp(CatalogRepositoryContract::class);
        $catalogRepository->setFilters(['template' => self::TEMPLATE]);
        $mappings = $catalogRepository->all();
        $id = $mappings->getResult()[0]['id'];

        $this->catalogExportService = $catalogExportRepository->exportById($id);
        $this->catalogExportService->setSettings(['marketId' => $this->settingshelper->get($this->settingshelper::SETTINGS_ORDER_REFERRER)]);
        $this->catalogExportService->setAdditionalFields(self::ADDITIONAL_FIELDS);
    }

    /**
     * Run the batch service.
     */
    final public function run()
    {
        $result = $this->catalogExportService->getResult();

       foreach ($result as $page) {
           $this->export($page);
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