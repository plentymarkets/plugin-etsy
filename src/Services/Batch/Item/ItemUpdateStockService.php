<?php

namespace Etsy\Services\Batch\Item;

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
    private $updateListingStockService;

    /**
     * @var DeleteListingService
     */
    private $deleteListingService;

    /**
     * @var AccountHelper
     */
    private $accountHelper;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

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
        OrderHelper $orderHelper)
    {
        $this->updateListingStockService = $updateListingStockService;
        $this->deleteListingService      = $deleteListingService;
        $this->accountHelper             = $accountHelper;
        $this->orderHelper               = $orderHelper;

        parent::__construct(pluginApp(VariationElasticSearchScrollRepositoryContract::class));
    }

    /**
     * Update all article which are not updated yet.
     *
     * @param array $variationElasticSearchScrollRepositoryResult
     *
     * @return void
     */
    protected function export(array $variationElasticSearchScrollRepositoryResult)
    {
        //todo do stuff

        /*

        if($this->accountHelper->isValidConfig())
        {
        $this->deleteDeprecatedListing();

        $this->updateListingsStock($records);
        }

         */
    }

    /**
     * Update listings on Etsy.
     *
     * @param RecordList $records
     */
    private function updateListingsStock(RecordList $records)
    {
        foreach($records as $record)
        {
            try
            {
                $this->updateListingStockService->updateStock($record);
            }
            catch(\Exception $ex)
            {
                $this->getLogger(__FUNCTION__)
                    ->setReferenceType('variationId')
                    ->setReferenceValue($record->variationBase->id)
                    ->error('Etsy::item.stockUpdateError', $ex->getMessage());
            }
        }
    }

    /**
     * Delete listings on Etsy and the entry in the market status table if the variation was deleted.
     */
    private function deleteDeprecatedListing()
    {
        $filter = [
            'marketId' => $this->orderHelper->getReferrerId(),
        ];

        /** @var VariationSkuRepositoryContract $variationSkuRepo */
        $variationSkuRepo = pluginApp(VariationSkuRepositoryContract::class);

        $variationSkuList = $variationSkuRepo->search($filter);

        /** @var VariationSku $variationSku */
        foreach($variationSkuList as $variationSku)
        {
            if($variationSku->deletedAt)
            {
                if($this->deleteListingService->delete($variationSku->sku))
                {
                    $variationSkuRepo->delete((int) $variationSku->id);
                }
            }
        }
    }
}
