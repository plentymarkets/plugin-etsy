<?php
namespace Etsy\Services\Item;

use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Helper\OrderHelper;
use Etsy\Api\Services\ListingService;
use Etsy\Helper\ItemHelper;
use Plenty\Plugin\Log\Loggable;

/**
 * Class UpdateListingStockService
 */
class UpdateListingStockService
{
	use Loggable;

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
	 * @param ItemHelper     $itemHelper
	 * @param OrderHelper    $orderHelper
	 * @param ListingService $listingService
	 */
	public function __construct(ItemHelper $itemHelper, OrderHelper $orderHelper, ListingService $listingService)
	{
		$this->itemHelper     = $itemHelper;
		$this->orderHelper    = $orderHelper;
		$this->listingService = $listingService;
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
				$quantity = $this->itemHelper->getStock($record) > 0 ? $this->itemHelper->getStock($record) : 1;
				$data = [
					'listing_id' => (int) $listingId,
					'state'      => $this->isVariationAvailable($record) ? ListingService::STATE_ACTIVE : ListingService::STATE_INACTIVE,
					'quantity'   => (int) $quantity,
					'price'      => number_format($record->variationRetailPrice->price, 2),
				];

				$this->getLogger(__FUNCTION__)
					->addReference('etsyListingId', $listingId)
					->addReference('variationId', $record->variationBase->id)
					->info('Etsy::item.preparingStockUpdate', $data);

				$this->listingService->updateListing($listingId, $data);

				$this->getLogger(__FUNCTION__)
					->addReference('etsyListingId', $listingId)
					->addReference('variationId', $record->variationBase->id)
					->report('Etsy::item.stockUpdateSuccess', $data);
			}
			catch(\Exception $ex)
			{
				$this->getLogger(__FUNCTION__)
					->addReference('etsyListingId', $listingId)
					->addReference('variationId', $record->variationBase->id)
					->error('Etsy::item.stockUpdateError', $ex->getMessage());
			}
		}
		else
		{
			$this->getLogger(__FUNCTION__)
				->addReference('etsyListingId', $listingId)
				->addReference('variationId', $record->variationBase->id)
				->info('Etsy::item.stockUpdateError');
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
