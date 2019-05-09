<?php
/**
 * Created by IntelliJ IDEA.
 * User: henrymalicha
 * Date: 09.05.19
 * Time: 13:51
 */

namespace Etsy\Helper;


use Etsy\Api\Services\ListingService;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;

class OldListingHelper
{
    public function migrateOldListings() {
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);
        /** @var VariationSkuRepositoryContract $variationSkuRepository */
        $variationSkuRepository = pluginApp(VariationSkuRepositoryContract::class);
        /** @var ListingService $listingService */
        $listingService = pluginApp(ListingService::class);

        try {
            $marketId = $settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER);

            $filter = [
                'marketId' => $marketId
            ];

            $listings = $variationSkuRepository->search($filter);

            foreach ($listings as $listing) {
                // etsy listing id
                $listingId = $listing->sku;
                // primary key from plenty_item_variation_market_status
                $tableId = $listing->id;

                $response = $listingService->getListing($listingId);

                if ($response['results'][0]['state'] !== "removed") {
                    $listingService->deleteListing($listingId);
                }

                $variationSkuRepository->delete($tableId);
            }
        } catch (\Exception $exception) {
            $exception->getMessage();
        }
    }
}