<?php

namespace Etsy\Services\Batch\Item;

use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Plugin\Application;
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Item\DataLayer\Models\RecordList;

use Etsy\Services\Item\UpdateListingService;
use Etsy\Services\Batch\AbstractBatchService;
use Etsy\Factories\ItemDataProviderFactory;
use Etsy\Services\Item\StartListingService;

/**
 * Class ItemExportService
 */
class ItemExportService extends AbstractBatchService
{
	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @var StartListingService
	 */
	private $startService;

	/**
	 * @var UpdateListingService
	 */
	private $updateService;

	/**
	 * @param Application             $app
	 * @param ItemDataProviderFactory $itemDataProviderFactory
	 * @param StartListingService     $startService
	 * @param UpdateListingService    $updateService
	 */
	public function __construct(
		Application $app,
		ItemDataProviderFactory $itemDataProviderFactory,
		StartListingService $startService,
		UpdateListingService $updateService
	)
	{
		$this->app     = $app;
		$this->startService = $startService;
		$this->updateService = $updateService;

		parent::__construct($itemDataProviderFactory->make('export'));
	}

	/**
	 * Export all items.
	 * @param RecordList $records
	 * @return void
	 */
	protected function export(RecordList $records)
	{
		foreach($records as $record)
		{
			try
			{
				if($this->listingIsCreated($record))
				{
					$this->updateService->update($record);
				}
				else
				{
					$this->startService->start($record);
				}
			}
			catch(ValidationException $ex)
			{
				$messageBag = $ex->getMessageBag();

				if(!is_null($messageBag))
				{
					// $this->logger->log('Can not start listing: ...');
				}
			}
		}
	}

	/**
	 * Check if listing is created.
	 *
	 * @param Record $record
	 *
	 * @return bool
	 */
	private function listingIsCreated(Record $record):bool
	{
		if(strlen((string) $record->variationMarketStatus->sku) > 0)
		{
			return true;
		}

		return false;
	}
}
