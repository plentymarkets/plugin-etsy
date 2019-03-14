<?php

namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\EtsyServiceProvider;
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
     * @var ListingInventoryService
     */
    private $listingInventoryService;

    /**
     * @var ItemHelper
     */
    private $itemHelper;

    /**
     * @var ListingService
     */
    private $listingService;

    /**
     * @var SettingsHelper
     */
    private $settingsHelper;


    const SOLD_OUT = "sold_out";

    /**
     * UpdateListingStockService constructor.
     * @param VariationExportServiceContract $variationExportService
     * @param ListingInventoryService $listingInventoryService
     * @param ListingService $listingService
     * @param ItemHelper $itemHelper
     * @param SettingsHelper $settingsHelper
     */
    public function __construct(
        VariationExportServiceContract $variationExportService,
        ListingInventoryService $listingInventoryService,
        ListingService $listingService,
        ItemHelper $itemHelper,
        SettingsHelper $settingsHelper
    ) {
        $this->variationExportService = $variationExportService;
        $this->listingInventoryService = $listingInventoryService;
        $this->settingsHelper = $settingsHelper;
        $this->listingService = $listingService;
        $this->itemHelper = $itemHelper;
    }

    /**
     * @param array $listing
     * @return array|null
     * @throws \Exception
     */
    public function updateStock(array $listing)
    {
        $listingId = 0;

        foreach ($listing as $variation) {
            if (isset($variation['skus'][0]['parentSku'])) {
                $listingId = $variation['skus'][0]['parentSku'];
                break;
            }
        }

        $etsyListing = $this->listingService->getListing($listingId);
        $state = $etsyListing['results'][0]['state'];
        $renew = $etsyListing['results'][0]['should_auto_renew'];

        if ($state == self::SOLD_OUT && !$renew){
            $this->getLogger(__FUNCTION__)
                ->addReference('listingId', $listingId)
                ->addReference('itemId', $listing['main']['itemId'])
                ->report(EtsyServiceProvider::PLUGIN_NAME . '::log.soldOut',
                    EtsyServiceProvider::PLUGIN_NAME . '::log.needManualRenew');
        }

        $etsyInventory = $this->listingInventoryService->getInventory($listingId);

        $products = $etsyInventory['results']['products'];

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

        $hasPositiveStock = false;

        foreach ($products as $key => $product) {
            if (!isset($product['sku']) || !$product['sku'])
            {
                //todo exception handling
                throw new \Exception('variation not in plenty');
            }

            $variationStillAvailable = false;

            foreach ($listing as $variation) {
                if ($variation['skus'][0]['sku'] != $product['sku']) {
                    continue;
                }
                $stock =  $variationExportService->getData($variationExportService::STOCK, $variation['variationId']);
                $stock = $stock[0]['stockNet'];
                $products[$key]['offerings'][0]['is_enabled'] = false;

                if ($stock > 0) {
                    $products[$key]['offerings'][0]['is_enabled'] = true;
                    $hasPositiveStock = true;
                }

                $products[$key]['offerings'][0]['quantity'] = $stock;
                $variationStillAvailable = true;
            }

            if (!$variationStillAvailable) {
                unset($products[$key]);
            }
        }

        if (!$hasPositiveStock && $state == self::SOLD_OUT) {
            return null;
        }

        $data = $etsyInventory['results'];
        $data['products'] = json_encode($products);

        $response = $this->listingInventoryService->updateInventory($listingId, $data);

        if (isset($response['error']) && $response['error']) {
            //todo übersetzen
            $message = 'Updating stock for listing ' . $listing['main']['skus'][0]['parentSku'] . ' failed.';

            if (isset($response['error_msg'])) {
                $message .= PHP_EOL . $response['error_msg'];
            }

            throw new \Exception($message);
        }

        foreach ($response['results']['products'] as $variation)
        {
            $status = $this->itemHelper::SKU_STATUS_INACTIVE;

            if ($hasPositiveStock) {
                $status = ($variation['offerings'][0]['quantity'] > 0) ? $this->itemHelper::SKU_STATUS_ACTIVE
                    : $this->itemHelper::SKU_STATUS_INACTIVE;
            }

            $matches = [];
            if (!preg_match('@(?!.*-)(.*)@', $variation['sku'], $matches)) {
                //todo
                throw new \Exception('');
            }

            /** @var array $matches */
            $variationId = $matches[0];

            $this->itemHelper->updateVariationSkuStatus($variationId, $status);
        }

        return $response;
    }
}
