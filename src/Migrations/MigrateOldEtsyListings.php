<?php

namespace Etsy\Migrations;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\Api\Services\ListingService;
use Etsy\EtsyServiceProvider;
use Etsy\Helper\ItemHelper;
use Etsy\Helper\SettingsHelper;
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
            $listingId = $listing->sku;

            $etsyListing = $listingInventoryService->getInventory($listingId)['results'];
            $etsyListing['products'][0]['sku'] = $listingId . '-' . $listing->variationId;
            $data['should_auto_renew'] = false;

            if ($etsyListing['products'][0]['offerings'][0]['quantity']) {
                $data['state'] = 'inactive';
                $listing->status = ItemHelper::SKU_STATUS_INACTIVE;
                $etsyListing['products'][0]['offerings'][0]['quantity'] = 1;
            }

            $listingService->updateListing($listingId, $data);

            $etsyListing['products'] = json_encode($etsyListing['products']);

            try {
                if (isset($listing['plenty_item_variation_market_status_deleted_timestamp'])) continue;

                $listing->sku = $listingId . '-' . $listing->variationId;
                $listing->parentSku = $listingId;
                $listing->save();

            } catch (\Throwable $exception) {
                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $listing->variationId)
                    ->error('Migration failed');
            }
        }
    }
}