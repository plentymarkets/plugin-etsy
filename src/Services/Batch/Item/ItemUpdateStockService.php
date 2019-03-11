<?php

namespace Etsy\Services\Batch\Item;

use Etsy\Helper\ImageHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Item\Variation\Models\Variation;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Item\DataLayer\Models\RecordList;

use Etsy\Helper\AccountHelper;
use Etsy\Helper\OrderHelper;
use Etsy\Services\Item\DeleteListingService;
use Etsy\Services\Item\UpdateListingStockService;
use Etsy\Services\Batch\AbstractBatchService;
use Plenty\Plugin\Application;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ItemUpdateStockService
 */
class ItemUpdateStockService extends AbstractBatchService
{
    use Loggable;

    /**
     * @var UpdateListingStockService
     */
    protected $updateListingStockService;

    /**
     * @var DeleteListingService
     */
    protected $deleteListingService;

    /**
     * @var AccountHelper
     */
    protected $accountHelper;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @param UpdateListingStockService      $updateListingStockService
     * @param DeleteListingService           $deleteListingService
     * @param AccountHelper                  $accountHelper
     * @param OrderHelper                    $orderHelper
     */
    public function __construct(
        UpdateListingStockService $updateListingStockService,
        DeleteListingService $deleteListingService,
        AccountHelper $accountHelper,
        OrderHelper $orderHelper,
        ImageHelper $imageHelper,
        Application $application)
    {
        $this->updateListingStockService = $updateListingStockService;
        $this->deleteListingService      = $deleteListingService;
        $this->accountHelper             = $accountHelper;
        $this->orderHelper               = $orderHelper;
        $this->imageHelper               = $imageHelper;

        parent::__construct(pluginApp(VariationElasticSearchScrollRepositoryContract::class), $application);
    }

    /**
     * Update all article which are not updated yet.
     *
     * @param array $catalogResult
     *
     * @return void
     */
    protected function export(array $catalogResult)
    {
        try {
            $this->deleteDeprecatedListing();
        } catch (\Exception $exception){

        }

        $listings = [];

        foreach ($catalogResult as $variation) {

            if ($variation['isMain'] == true) {
                $listings[$variation['itemId']]['main'] = $variation;
                continue;
            }
            $listings[$variation['itemId']][] = $variation;

        }

        foreach ($listings as $listing) {
            try {
                $this->updateListingsStock($listing);

            } catch (\Exception $exception) {
                $test = true;
            }
        }
    }

    /**
     * Update listings on Etsy.
     *
     * @param RecordList $records
     */
    protected function updateListingsStock(array $listing)
    {
            try
            {
                $response = $this->updateListingStockService->updateStock($listing);

                if (isset($response['error']) && $response['error']) {
                    //todo übersetzen
                    $message = 'Updating stock for listing ' . $listing['main']['skus'][0]['parentSku'] . ' failed.';

                    if (isset($response['error_msg'])) {
                        $message .= PHP_EOL . $response['error_msg'];
                    }

                    throw new \Exception($message);
                }
            }
            catch(\Exception $ex)
            {
                $this->getLogger(__FUNCTION__)
                    ->setReferenceType('variationId')
                    ->setReferenceValue($listing)
                    ->error('Etsy::item.stockUpdateError', $ex->getMessage());
            }
    }

    /**
     * Delete listings on Etsy and the entry in the market status table if the variation was deleted.
     */
    protected function deleteDeprecatedListing()
    {
        $filter = [
            'marketId' => $this->settingshelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER)
        ];

        /** @var VariationSkuRepositoryContract $variationSkuRepository */
        $variationSkuRepository = pluginApp(VariationSkuRepositoryContract::class);

        $variationSkuList = $variationSkuRepository->search($filter);

        //each listings id linked to true or false -> true means that the listing has active variations
        $listings = [];

        /** @var VariationSku $variationSku */
        foreach ($variationSkuList as $key => $variationSku)
        {
            if (!isset($listings[$variationSku->parentSku])) {
                $listings[$variationSku->parentSku] = false;
            }

            if (!isset($variationSku->deletedAt)) {
                $listings[$variationSku->parentSku] = true;
                continue;
            }

            $variationSkuRepository->delete((int) $variationSku->id);
            $this->imageHelper->delete($variationSku->variationId);
            unset($variationSkuList[$key]);
        }

        foreach ($listings as $listingId => $hasVariations) {
            if (!$hasVariations) {
                $this->deleteListingService->delete($listingId);
            }
        }
    }
}
