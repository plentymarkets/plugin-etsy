<?php

namespace Etsy\Services\Batch\Item;

use Etsy\Helper\AccountHelper;
use Etsy\Helper\OrderHelper;
use Etsy\Services\Item\DeleteListingService;
use Etsy\Services\Item\UpdateListingStockService;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Item\DataLayer\Models\RecordList;

use Etsy\Logger\Logger;
use Etsy\Services\Batch\AbstractBatchService;
use Etsy\Factories\ItemDataProviderFactory;
use Etsy\Validators\UpdateListingValidator;
use Etsy\Services\Item\UpdateListingService;

/**
 * Class ItemUpdateStockService
 */
class ItemUpdateStockService extends AbstractBatchService
{
	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var UpdateListingStockService
	 */
	private $updateStockservice;

	/**
	 * @var DeleteListingService
	 */
	private $deleteService;

	/**
	 * @var AccountHelper
	 */
	private $accountHelper;

	/**
	 * @var OrderHelper
	 */
	private $orderHelper;

	/**
	 * @var VariationSkuRepositoryContract
	 */
	private $variationSkuRepositoryContract;

	/**
	 * @param Logger                     $logger
	 * @param ItemDataProviderFactory    $itemDataProviderFactory
	 * @param UpdateListingStockService  $updateStockservice
	 * @param AccountHelper              $accountHelper
	 */
	public function __construct(
		Logger $logger,
		ItemDataProviderFactory $itemDataProviderFactory,
		UpdateListingStockService $updateStockservice,
		DeleteListingService $deleteService,
		AccountHelper $accountHelper,
		OrderHelper $orderHelper,
		VariationSkuRepositoryContract $variationSkuRepositoryContract
	)
	{
		$this->logger                         = $logger;
		$this->updateStockservice             = $updateStockservice;
		$this->deleteService                  = $deleteService;
		$this->accountHelper                  = $accountHelper;
		$this->orderHelper                  = $orderHelper;
		$this->variationSkuRepositoryContract = $variationSkuRepositoryContract;

		parent::__construct($itemDataProviderFactory->make('update'));
	}

	/**
	 * Update all article which are not updated yet.
	 *
	 * @param RecordList $records
	 * @return void
	 */
	protected function export(RecordList $records)
	{
		// TODO: method isITemUpdateStockProces... needs to be implemented
		if($this->accountHelper->isValidConfig() && $this->accountHelper->isItemUpdateStockProcessActive())
		{
			$this->deleteDeprecatedListing();

			$this->updateListingsStock($records);
		}
	}

	/**
	 * Update listings on etsy.
	 *
	 * @param RecordList $records
	 */
	private function updateListingsStock(RecordList $records)
	{
		foreach($records as $record)
		{
			try
			{
//				UpdateListingStockValidator::validateOrFail([
//					// TODO fill here all data that we need for starting an etsy listing
//				]);

				$this->updateStockservice->updateStock($record);
			}
			catch(ValidationException $ex)
			{
				$messageBag = $ex->getMessageBag();

				if(!is_null($messageBag))
				{
					$this->logger->log('Can not update Stock for variation: ' . $record->variationBase->id);
				}
			}
		}
	}

	/**
	 * Deletes listings on Etsy and the entry in the market status table if the variation is deleted.
	 */
	private function deleteDeprecatedListing()
	{
		$filter = [
			'marketId' => $this->orderHelper->getReferrerId(),
		];

		$variationSkuList = $this->variationSkuRepositoryContract->search($filter);

		/** @var VariationSku $variationSku */
		foreach($variationSkuList as $variationSku)
		{
			if($variationSku->deletedTimestamp)
			{
				if($this->deleteService->delete($variationSku->variationSku))
				{
					$this->variationSkuRepositoryContract->delete((int) $variationSku->id);
				}
			}
		}
	}
}
