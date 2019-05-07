<?php

namespace Etsy\Migrations;


use Etsy\Api\Services\ListingService;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Item\VariationMarket\Repositories\VariationMarketRepository;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Item\VariationSku\Repositories\VariationSkuRepository;

class UpdateOldEtsyListings
{
    public function run()
    {
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);
        /** @var VariationSkuRepository $variationSkuRepository */
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