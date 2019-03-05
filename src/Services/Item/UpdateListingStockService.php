<?php

namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\Helper\SettingsHelper;
use modules\lib\calendar\lib\DAV\Exception;
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
     * @param ItemHelper $itemHelper
     * @param OrderHelper $orderHelper
     * @param ListingService $listingService
     */
    public function __construct(
        VariationExportServiceContract $variationExportService,
        ListingInventoryService $listingInventoryService,
        SettingsHelper $settingsHelper
    ) {
        $this->variationExportService = $variationExportService;
        $this->listingInventoryService = $listingInventoryService;
        $this->settingsHelper = $settingsHelper;
    }

    public function updateStock(array $listing)
    {

        $listingId = $listing['main']['skus'][0]['parentSku'];

        $etsyListing = $this->listingInventoryService->getInventory($listingId);

        $products = $etsyListing['results']['products'];


        $etsyListing['results']['products'][0]['sku'];

        $variationExportService = $this->variationExportService;

        $exportPreloadValueList = [];

        foreach ($listing as $variation)
        {
            $exportPreloadValue = pluginApp(ExportPreloadValue::class, [
                'itemId' => $variation['itemId'],
                'variationId' => $variation['variationId']
            ]);

            $exportPreloadValueList[] = $exportPreloadValue;
        }


        $variationExportService->addPreloadTypes([$variationExportService::STOCK]);
        $variationExportService->preload($exportPreloadValueList);


        foreach ($products as $key => $product) {
            if (!isset($product['sku']) || !$product['sku'])
            {
                //todo exception handling
                throw new \Exception('variation not in plenty');
            }
            foreach ($listing as $variation) {
                if ($variation['skus'][0]['sku'] != $product['sku']) {
                    continue;
                }
                $products[$key]['offerings'][0]['quantity'] = $variationExportService->getData($variationExportService::STOCK, $variation['variationId']);
            }
        }

        $data = [];
        $data['products'] = json_encode($products);

        $this->listingInventoryService->updateInventory($listingId, $data);
    }
}
