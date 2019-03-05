<?php
namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Helper\OrderHelper;
use Etsy\Api\Services\ListingService;
use Etsy\Helper\ItemHelper;
use Plenty\Modules\Item\Variation\Contracts\VariationExportServiceContract;
use Plenty\Modules\Item\Variation\Services\ExportPreloadValue\ExportPreloadValue;
use Plenty\Plugin\Log\Loggable;

/**
 * Class UpdateListingStockService
 */
class UpdateListingStockService
{
	use Loggable;

    /**
     * @var $variationExportService
     */
    private $variationExportService;

    /**
     * @var listingInventoryService
     */
    private $listingInventoryService;

    /**
     * @var SettingsHelper
     */
    private $settingsHelper;

	/**
	 * @param ItemHelper     $itemHelper
	 * @param OrderHelper    $orderHelper
	 * @param ListingService $listingService
	 */
	public function __construct(VariationExportServiceContract $variationExportService,
                                ListingInventoryService $listingInventoryService,
                                SettingsHelper $settingsHelper)
	{
	    $this->variationExportService = $variationExportService;
	    $this->listingInventoryService = $listingInventoryService;
	    $this->settingsHelper = $settingsHelper;
	}

	public function updateStock(array $listing)
	{

//	    $listingId = $listing['skus'][0]['parentSku'];

        $listingId = 674256898;

        $test = $this->listingInventoryService->getInventory($listingId);

        $variationExportService = $this->variationExportService;

        $exportPreloadValueList = [];

            $exportPreloadValue = pluginApp(ExportPreloadValue::class, [
                'itemId' => $listing['itemId'],
                'variationId' => $listing['variationId']
            ]);

            $exportPreloadValueList[] = $exportPreloadValue;



            $variationExportService->addPreloadTypes([$variationExportService::STOCK]);
            $variationExportService->preload($exportPreloadValueList);
            $stock = $variationExportService->getData($variationExportService::STOCK, $listing['variationId']);



            $this->listingInventoryService->updateInventory($listingId, $stock[0]);



	    //todo
		/*
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
		 */
	}


	/**
	 * Checks if a variation is active and visible for the Etsy marketplace.
	 *
	 * @param Record $record
	 *
	 * @return bool
	 */
//	private function isVariationAvailable(Record $record)
//	{
//		if(!$record->variationBase->active)
//		{
//			return false;
//		}
//
//		if($this->itemHelper->getStock($record) <= 0)
//		{
//			return false;
//		}
//
//		foreach($record->variationLinkMarketplace as $marketLink)
//		{
//			if($marketLink->marketplaceId == $this->orderHelper->getReferrerId())
//			{
//				return true;
//			}
//		}
//
//		return false;
//	}
}
