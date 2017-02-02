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
use Plenty\Plugin\Log\Loggable;

/**
 * Class ItemExportService
 */
class ItemExportService extends AbstractBatchService
{
	use Loggable;

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
				if($this->isListingCreated($record))
				{
					$this->updateService->update($record);
				}
				else
				{
					$this->startService->start($record);
				}
			}
			catch(\Exception $ex)
			{
				$this->getLogger(__FUNCTION__)->error('Etsy::item.startListingError', $ex->getMessage());
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
	private function isListingCreated(Record $record):bool
	{
		$listingId = (string) $record->variationMarketStatus->sku;

		if(strlen($listingId))
		{
			return true;
		}

		return false;
	}
}
