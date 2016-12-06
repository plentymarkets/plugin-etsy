<?php

namespace Etsy\Services\Batch\Item;

use Etsy\Services\Item\UpdateListingService;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Plugin\Application;
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Item\DataLayer\Models\RecordList;

use Etsy\Logger\Logger;
use Etsy\Services\Batch\AbstractBatchService;
use Etsy\Factories\ItemDataProviderFactory;
use Etsy\Validators\StartListingValidator;
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
	 * @var Logger
	 */
	private $logger;

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
	 * @param Logger                  $logger
	 * @param ItemDataProviderFactory $itemDataProviderFactory
	 * @param StartListingService     $startService
	 * @param UpdateListingService    $updateService
	 */
	public function __construct(
		Application $app,
		Logger $logger,
		ItemDataProviderFactory $itemDataProviderFactory,
		StartListingService $startService,
		UpdateListingService $updateService
	)
	{
		$this->app     = $app;
		$this->logger  = $logger;
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
				StartListingValidator::validateOrFail([
					                                      // TODO fill here all data that we need for starting an etsy listing
				                                      ]);

				// TODO: add if(isExportProcessActive from helper class here)
				if($this->isAlreadyCreated($record))
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
					$this->logger->log('Can not start listing: ...');
				}
			}
		}
	}

	/**
	 * Check if listing is already created.
	 *
	 * @param Record $record
	 *
	 * @return bool
	 */
	private function isAlreadyCreated(Record $record):bool
	{
		if(strlen((string) $record->variationMarketStatus->sku) > 0)
		{
			return true;
		}

		return false;
	}
}
