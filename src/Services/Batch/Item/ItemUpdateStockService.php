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
        ImageHelper $imageHelper)
    {
        $this->updateListingStockService = $updateListingStockService;
        $this->deleteListingService      = $deleteListingService;
        $this->accountHelper             = $accountHelper;
        $this->orderHelper               = $orderHelper;
        $this->imageHelper               = $imageHelper;

        parent::__construct(pluginApp(VariationElasticSearchScrollRepositoryContract::class));
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
}
