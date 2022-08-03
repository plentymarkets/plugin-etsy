<?php

namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\EtsyServiceProvider;
use Etsy\Exceptions\ListingException;
use Etsy\Helper\SettingsHelper;
use Illuminate\Support\MessageBag;
use Etsy\Api\Services\ListingService;
use Etsy\Helper\ItemHelper;
use Plenty\Modules\Item\Variation\Contracts\VariationExportServiceContract;
use Plenty\Modules\Item\Variation\Services\ExportPreloadValue\ExportPreloadValue;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;

/**
 * Class UpdateListingStockService
 */
class UpdateListingStockService
{
    use Loggable;

    /**
     * @var $variationExportService
     */
    protected $variationExportService;

    /**
     * @var ListingInventoryService
     */
    protected $listingInventoryService;

    /**
     * @var ItemHelper
     */
    protected $itemHelper;

    /**
     * @var ListingService
     */
    protected $listingService;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * String values which can be used in properties to represent true
     */
    const BOOL_CONVERTIBLE_STRINGS = ['1', 'y', 'true'];

    /**
     * state of sold out listings
     */
    const SOLD_OUT = "sold_out";

    /**
     * state of sold out listings
     */
    const EXPIRED = "expired";

    /**
     * State of active listings
     */
    const ACTIVE = "active";

    /**
     * State etsy expects to make a listing inactive
     */
    const INACTIVE = "inactive";

    /**
     * State etsy returns if a listing is inactive
     */
    const EDIT = "edit";

    /**
     * Maximum value of stock allowed from etsy
     */
    const MAXIMUM_ALLOWED_STOCK = 999;

    /**
     * UpdateListingStockService constructor.
     * @param VariationExportServiceContract $variationExportService
     * @param ListingInventoryService $listingInventoryService
     * @param ListingService $listingService
     * @param ItemHelper $itemHelper
     * @param SettingsHelper $settingsHelper
     * @param Translator $translator
     */
    public function __construct(
        VariationExportServiceContract $variationExportService,
        ListingInventoryService $listingInventoryService,
        ListingService $listingService,
        ItemHelper $itemHelper,
        SettingsHelper $settingsHelper,
        Translator $translator
    ) {
        $this->variationExportService = $variationExportService;
        $this->listingInventoryService = $listingInventoryService;
        $this->settingsHelper = $settingsHelper;
        $this->listingService = $listingService;
        $this->itemHelper = $itemHelper;
        $this->translator = $translator;
    }

    /**
     * @param array $listing
     * @throws \Exception
     */
    public function updateStock(array $listing)
    {
        $listingId = 0;

        foreach ($listing as $variation) {
            if (isset($variation['skus'][0]['parentSku'])) {
                $listingId = (int)$variation['skus'][0]['parentSku'];
                break;
            }
        }

        if ($listingId === 0) /* todo: exception? */ {
            return;
        }

        try {
            $etsyListing = $this->listingService->getListing($listingId);
            $this->getLogger(EtsyServiceProvider::STOCK_UPDATE_SERVICE)
                ->addReference('listingId', $listingId)
                ->report(EtsyServiceProvider::PLUGIN_NAME . '::item.itemExportListings', [
                    'function' => 'inside_updateStock',
                    'etsyListing' => $etsyListing
                ]);
            $state = $etsyListing['results'][0]['state'];

            $renew = true;

            if (isset($listing['main']['renew'])) {
                $renew = in_array($listing['main']['renew'], self::BOOL_CONVERTIBLE_STRINGS);
            }

            $didListingEnd = $etsyListing['results'][0]['ending_tsz'] < time();

            if ((
                $state == self::SOLD_OUT
                    || $state == self::EXPIRED
                    || $didListingEnd
                ) && !$renew) {
                $this->getLogger(__FUNCTION__)
                    ->addReference('listingId', $listingId)
                    ->addReference('itemId', $listing['main']['itemId'])
                    ->report(EtsyServiceProvider::PLUGIN_NAME . '::log.soldOut',
                        EtsyServiceProvider::PLUGIN_NAME . '::log.needManualRenew');
            }

            $products = $this->update($listingId, $listing);
            $this->getLogger(__FUNCTION__)
                ->addReference('itemId', $listing['main']['itemId'])
                ->report(EtsyServiceProvider::PLUGIN_NAME . '::item.itemExportListings', [
                    'function' => 'inside_updateStock',
                    'products' => $products
                ]);

            //no positive stock
            if (is_null($products)) {
                $this->getLogger(__FUNCTION__)
                    ->addReference('itemId', $listing['main']['itemId'])
                    ->addReference('listingId', $listingId)
                    ->report('updateStock Product null', [
                        'function' => 'inside_updateStock',
                        'data' => 'no data'
                    ]);
                if ($state != self::ACTIVE) {
                    return;
                }

                //since etsy can't handle a stock of 0 we declare the listing inactive
                $this->listingService->updateListing($listingId, ['state' => 'inactive']);

                foreach ($listing as $variation) {
                    $sku = $this->itemHelper->getVariationSku($variation['variationId']);

                    if ($sku->status != $this->itemHelper::SKU_STATUS_ACTIVE) {
                        continue;
                    }

                    $this->itemHelper->updateVariationSkuStatus($variation['variationId'], $this->itemHelper::SKU_STATUS_INACTIVE);
                }
                return;
            }

            //new state of the listing
            $newState = self::INACTIVE;

            foreach ($products as $variation) {
                $matches = [];
                if (!preg_match('@^([0-9]+)-([0-9]+)$@', $variation['sku'], $matches)) {
                    //given variation has no usable sku
                    $this->getLogger(EtsyServiceProvider::LISTING_UPDATE_STOCK_SERVICE)
                        ->addReference('listingId', $listingId)
                        ->report(EtsyServiceProvider::PLUGIN_NAME . '::log.unknownEtsyVariation', $variation);

                    continue;
                }

                /** @var array $matches */
                $variationId = $matches[2];

                $sku = $this->itemHelper->getVariationSku($variationId);

                if (!$sku) {
                    //todo translate
                    throw new \Exception('given variation has no sku');
                }

                $this->itemHelper->updateVariationSkuStockTimestamp($variationId);

                //variations with errors can be ignored
                if ($sku->status == $this->itemHelper::SKU_STATUS_ERROR) {
                    continue;
                }

                $status = $this->itemHelper::SKU_STATUS_INACTIVE;

                if ((int) $variation['offerings'][0]['quantity'] > 0) {
                    $status = $this->itemHelper::SKU_STATUS_ACTIVE;
                    $newState = self::ACTIVE;
                }

                $this->itemHelper->updateVariationSkuStatus($variationId, $status);
            }

            $data = [
                'state' => $newState
            ];

            //we have positiv stock and the listing was sold out or expired
            if ((
                    $state == self::SOLD_OUT
                    || $state == self::EXPIRED
                    || $didListingEnd
                ) && $renew) {
                $data['renew'] = true;
                $this->getLogger(__FUNCTION__)
                    ->addReference('listingId', $listingId)
                    ->addReference('itemId', $listing['main']['itemId'])
                    ->debug(EtsyServiceProvider::PLUGIN_NAME . '::log.soldOut');
            }

            $this->getLogger(__FUNCTION__)
                ->addReference('itemId', $listing['main']['itemId'])
                ->addReference('listingId', $listingId)
                ->report('updateStock Has Product', [
                    'function' => 'inside_updateStock',
                    'data' => $data
                ]);

            $this->listingService->updateListing($listingId, $data);
            $this->getLogger(__FUNCTION__)
                ->addReference('listingId', $listingId)
                ->addReference('itemId', $listing['main']['itemId'])
                ->debug(EtsyServiceProvider::PLUGIN_NAME . '::log.successfullyUpdatedStock');
        } catch (\Exception $data) {
            $this->getLogger(__FUNCTION__)
                ->addReference('itemId', $listing['main']['itemId'])
                ->addReference('listingId', $listingId)
                ->report('updateStock Has Exception', [
                    'function' => 'inside_updateStock',
                    'data' => 'no data',
                    'error' => $e->getMessage()
                ]);
            $this->listingService->updateListing($listingId, ['state' => 'inactive']);

            foreach ($listing as $variation) {
                $sku = $this->itemHelper->getVariationSku($variation['variationId']);

                if ($sku->status != $this->itemHelper::SKU_STATUS_ACTIVE) {
                    continue;
                }

                $this->itemHelper->updateVariationSkuStatus($variation['variationId'], $this->itemHelper::SKU_STATUS_INACTIVE);
            }
            throw $e;
        }
    }

    /**
     * @param $listingId
     * @param array $listing
     * @return array|null
     * @throws ListingException
     * @throws \Exception
     */
    protected function update($listingId, array $listing)
    {
        $this->getLogger(EtsyServiceProvider::STOCK_UPDATE_SERVICE)
            ->addReference('listingId', $listingId)
            ->info(EtsyServiceProvider::PLUGIN_NAME . '::item.itemExportListings', [
                'function' => 'updateStock->update',
                'listing' => $listing
            ]);
        $retrys = 3;
        $etsyInventory = null;

        //we retry reading the inventory since we've already successfully loaded the listing itself at this point
        //which means the inventory must be loadable. An error at this point probably is caused by connection issues
        for ($counter = 0; $counter < $retrys; $counter++) {
            $etsyInventory = $this->listingInventoryService->getInventory($listingId);
            $this->getLogger(EtsyServiceProvider::STOCK_UPDATE_SERVICE)
                ->addReference('listingId', $listingId)
                ->info('EtsyInventory', [
                    'function' => 'updateStock->update',
                    'etsyInventoryResponse' => $etsyInventory
                ]);
            if (isset($etsyInventory['results']) && is_array($etsyInventory['results'])) {
                break;
            }
        }

        if (!isset($etsyInventory['results']) || !is_array($etsyInventory['results'])) {
            $messages = [];

            if (is_array($etsyInventory) && isset($etsyInventory['error_msg'])) {
                $messages[] = $etsyInventory['error_msg'];
            } else {
                if (is_string($etsyInventory)) {
                    $messages[] = $etsyInventory;
                } else {
                    $messages[] = $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.emptyResponse');
                }
            }

            $messageBag = pluginApp(MessageBag::class, ['messages' => $messages]);
            throw new ListingException($messageBag,
                $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.updateStockError'));
        }

        $products = $etsyInventory['results']['products'];

        $variationExportService = $this->variationExportService;

        $exportPreloadValueList = [];

        foreach ($listing as $variation) {
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
            if (!isset($product['sku']) || !$product['sku']) {
                //todo translate
                throw new \Exception('variation not in plenty. Product id ' . $product['product_id']);
            }

            $variationStillAvailable = false;

            foreach ($listing as $variation) {
                if (!isset($variation['skus'][0]['sku']) || $variation['skus'][0]['sku'] != $product['sku']) {
                    continue;
                }
                //todo reactivate this feature when we have a solution for shipping time depending on quantity sold
//                if ($variation['stockLimitation'] === StartListingService::NO_STOCK_LIMITATION_) {
//                    $stock = self::MAXIMUM_ALLOWED_STOCK;
//                } else {
//                    $stock = $variationExportService->getData($variationExportService::STOCK, $variation['variationId']);
//                    $stock = $stock[0]['stockNet'];
//                    // etsy only takes int´s as quantity, so we round it down. For example 9,5 will now be 9
//                    $stock = round($stock, 0, PHP_ROUND_HALF_DOWN);
//                }

                $stock = $variationExportService->getData($variationExportService::STOCK, $variation['variationId']);
                $stock = $stock[0]['stockNet'];
                // etsy only takes int´s as quantity, so we round it down. For example 9,5 will now be 9
                $stock = (int) $stock;

                if ($stock > 0 && $variation['skus'][0]['status'] != $this->itemHelper::SKU_STATUS_ERROR) {
                    $hasPositiveStock = true;
                    $products[$key]['offerings'][0]['is_enabled'] = true;
                }

                if ($stock > self::MAXIMUM_ALLOWED_STOCK) {
                    $stock = self::MAXIMUM_ALLOWED_STOCK;
                }

                $products[$key]['offerings'][0]['quantity'] = $stock >= 0 ? $stock : 0;
                $variationStillAvailable = true;
            }

            if (!$variationStillAvailable) {
                unset($products[$key]);
            }
        }

        if (!$hasPositiveStock) {
            return null;
        }

        $data = $etsyInventory['results'];
        $data['products'] = json_encode($products);

        $response = $this->listingInventoryService->updateInventory($listingId, $data);
        $this->getLogger(EtsyServiceProvider::STOCK_UPDATE_SERVICE)
            ->addReference('listingId', $listingId)
            ->info('EtsyInventoryUpdate', [
                'function' => 'updateStock->update',
                'etsyInventoryUpdateResponse' => $response
            ]);
        if (!isset($response['results']) || !is_array($response['results'])) {
            $messages = [];

            if (is_array($response) && isset($response['error_msg'])) {
                $messages[] = $response['error_msg'];
            } else {
                if (is_string($response)) {
                    $messages[] = $response;
                } else {
                    $messages[] = $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.emptyResponse');
                }
            }

            $messageBag = pluginApp(MessageBag::class, ['messages' => $messages]);

            throw new ListingException($messageBag,
                $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.stockUpdateError'));
        }


        $products = json_decode(json_encode($response['results']['products']), true);

        return $products;
    }

}
