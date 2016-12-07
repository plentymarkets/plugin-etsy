<?php
namespace Etsy\Services\Item;

use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Helper\OrderHelper;
use Etsy\Api\Services\ListingService;
use Etsy\Helper\ItemHelper;
use Etsy\Logger\Logger;

/**
 * Class UpdateListingStockService
 */
class UpdateListingStockService
{
	/**
	 * @var ItemHelper
	 */
	private $itemHelper;

	/**
	 * @var OrderHelper
	 */
	private $orderHelper;

	/**
	 * @var ListingService
	 */
	private $listingService;

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @param ItemHelper       $itemHelper
	 * @param OrderHelper      $orderHelper
	 * @param ListingService   $listingService
	 * @param Logger           $logger
	 */
	public function __construct(
		ItemHelper $itemHelper,
		OrderHelper $orderHelper,
		ListingService $listingService,
		Logger $logger)
	{
		$this->itemHelper     = $itemHelper;
		$this->orderHelper    = $orderHelper;
		$this->listingService = $listingService;
		$this->logger         = $logger;
	}

	/**
	 * Updates
	 *
	 * @param Record $record
	 */
	public function updateStock(Record $record)
	{
		$listingId = $record->variationMarketStatus->sku;

		if(!is_null($listingId))
		{
			try
			{
				$data = [
					'listing_id' => (int) $listingId,
					'state'      => $this->isVariationAvailable($record) ? ListingService::STATE_ACTIVE : ListingService::STATE_INACTIVE,
					'quantity'   => $this->itemHelper->getStock($record) > 0 ? $this->itemHelper->getStock($record) : 1,
				];

				$this->listingService->updateListing($listingId, $data);
			}
			catch(\Exception $e)
			{
				$this->logger->log('Could not update listing stock for variation id ' . $record->variationBase->id . ': ' . $e->getMessage());
			}
		}
		else
		{
			$this->logger->log('Could not update listing stock for variation id: ' . $record->variationBase->id);
		}
	}

	/**
	 * Checks if a variation is active and visible for the Etsy marketplace.
	 *
	 * @param Record $record
	 *
	 * @return bool
	 */
	private function isVariationAvailable(Record $record)
	{
		if(!$record->variationBase->active)
		{
			return false;
		}

		if($this->itemHelper->getStock($record) <= 0)
		{
			return false;
		}

		foreach($record->variationLinkMarketplace as $marketLink)
		{
			if($marketLink->marketplaceId == $this->orderHelper->getReferrerId())
			{
				return true;
			}
		}

		return false;
	}
}
