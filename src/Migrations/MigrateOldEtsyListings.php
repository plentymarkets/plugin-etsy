<?php

namespace Etsy\Migrations;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\Api\Services\ListingService;
use Etsy\EtsyServiceProvider;
use Etsy\Helper\ItemHelper;
use Etsy\Helper\SettingsHelper;
use Etsy\Services\Item\UpdateListingService;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Plugin\Log\Loggable;

/**
 * @author H.Malicha
 * Class MigrateOldEtsyListings
 * @package Etsy\Migrations
 */
class MigrateOldEtsyListings
{
    use Loggable;

    public function run()
    {
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);
        /** @var VariationSkuRepositoryContract $variationSkuRepository */
        $variationSkuRepository = pluginApp(VariationSkuRepositoryContract::class);
        /** @var ListingInventoryService $listingInventoryService */
        $listingInventoryService = pluginApp(ListingInventoryService::class);
        /** @var ListingService $listingService */
        $listingService = pluginApp(ListingService::class);

        $filter = [
            'marketId' => $settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER)
        ];

        $listings = $variationSkuRepository->search($filter);

        /** @var VariationSku $listing */
        foreach ($listings as $listing) {
            if (isset($listing['plenty_item_variation_market_status_deleted_timestamp'])) continue;
            $listingId = $listing->sku;

            $listing->sku = $listingId . '-' . $listing->variationId;
            $listing->parentSku = $listingId;

            try {
                $currentState = $listingService->getListing($listingId)['results'][0];
                $data['should_auto_renew'] = false;
                $data['write_missing_inventory'] = true;
                // draft-listings can't be set to inactive, so we have to check that
                $data['state'] = $currentState['state'] == 'draft' ? 'draft' : 'inactive';

                $response = $listingService->updateListing($listingId, $data);

                if (!isset($response['results']) || !is_array($response['results'])) {
                    throw new \Exception();
                }

                $etsyListing = $listingInventoryService->getInventory($listingId)['results'];
                $etsyListing['products'][0]['sku'] = $listingId . '-' . $listing->variationId;

                $quantity =  $etsyListing['products'][0]['offerings'][0]['quantity'];
                $price =  (float) ($etsyListing['products'][0]['offerings'][0]['price']['amount'] /
                    $etsyListing['products'][0]['offerings'][0]['price']['divisor']);
                $price = round($price, UpdateListingService::MONEY_DECIMALS);

                if ($etsyListing['products'][0]['offerings'][0]['quantity'] < 1) {
                    $listing->status = ItemHelper::SKU_STATUS_INACTIVE;
                    $quantity = 1;
                }

                $etsyListing['products'][0]['offerings'] = [
                    [
                        'quantity' => $quantity,
                        'price' => $price
                    ]
                ];

                $etsyListing['products'] = json_encode($etsyListing['products']);

                $response = $listingInventoryService->updateInventory($listingId, $etsyListing);

                if (!isset($response['results']) || !is_array($response['results'])) {
                    throw new \Exception();
                }

                $listing->save();
            } catch (\Throwable $exception) {
                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $listing->variationId)
                    ->error('Migration failed');
            }
        }
    }
}