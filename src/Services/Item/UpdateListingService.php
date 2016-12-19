<?php
namespace Etsy\Services\Item;

use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Api\Services\ListingService;
use Etsy\Helper\ItemHelper;

/**
 * Class UpdateListingService
 */
class UpdateListingService
{
	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @var ItemHelper
	 */
	private $itemHelper;

	/**
	 * @var ListingService
	 */
	private $listingService;

	/**
	 * @param ItemHelper       $itemHelper
	 * @param ConfigRepository $config
	 * @param ListingService   $listingService
	 */
	public function __construct(ItemHelper $itemHelper, ConfigRepository $config, ListingService $listingService)
	{
		$this->itemHelper     = $itemHelper;
		$this->config         = $config;
		$this->listingService = $listingService;
	}

	/**
	 * @param Record $record
	 */
	public function update(Record $record)
	{
		$listingId = $record->variationMarketStatus->sku;

		if(!is_null($listingId))
		{
			$this->updateListing($record, $listingId);
		}
		else
		{
			// $this->logger->log('Could not start listing for variation id: ' . $record->variationBase->id);
		}

	}

	/**
	 * @param Record $record
	 * @param int    $listingId
	 */
	private function updateListing(Record $record, $listingId)
	{
		$data = [
			'listing_id' => $listingId,
			'quantity'   => $this->itemHelper->getStock($record),
			'price'      => $record->variationRetailPrice->price
		];

		$this->listingService->updateListing($listingId, $data);
	}
}
