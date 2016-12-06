<?php
namespace Etsy\Services\Item;

use Etsy\Helper\OrderHelper;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Api\Services\ListingService;
use Etsy\Helper\ItemHelper;
use Etsy\Logger\Logger;

/**
 * Class UpdateListingStockService
 */
class UpdateListingStockService
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
	 * @var OrderHelper
	 */
	private $orderHelper;

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var ListingService
	 */
	private $listingService;

	/**
	 * @var VariationSkuRepositoryContract
	 */
	private $variationSkuRepositoryContract;

	/**
	 * @param ItemHelper             $itemHelper
	 * @param OrderHelper            $orderHelper
	 * @param ConfigRepository       $config
	 * @param ListingService         $listingService
	 * @param Logger                 $logger
	 * @param VariationSkuRepositoryContract $variationSkuRepositoryContract
	 */
	public function __construct(
		ItemHelper $itemHelper,
		OrderHelper $orderHelper,
		ConfigRepository $config,
		ListingService $listingService,
		Logger $logger,
		VariationSkuRepositoryContract $variationSkuRepositoryContract
	)
	{
		$this->itemHelper                      = $itemHelper;
		$this->orderHelper                     = $orderHelper;
		$this->config                          = $config;
		$this->logger                          = $logger;
		$this->listingService                  = $listingService;
		$this->variationSkuRepositoryContract = $variationSkuRepositoryContract;
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
					'listing_id' => $listingId,
					'state'      => $this->isVariationAvailable($record) ? 'active' : 'inactive',
					'quantity'   => $this->itemHelper->getStock($record) > 0 ? $this->itemHelper->getStock($record) : 1,
				];

				$this->listingService->updateListingStock($listingId, $data);
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
