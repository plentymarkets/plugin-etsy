<?php
/**
 * Created by IntelliJ IDEA.
 * User: henrymalicha
 * Date: 09.05.19
 * Time: 13:51
 */

namespace Etsy\Helper;


use Etsy\Api\Services\ListingService;
use Etsy\EtsyServiceProvider;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Plugin\Log\Loggable;

class OldListingHelper
{
    use Loggable;

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
                $variationId = $listing->variationId;
                // etsy listing id
                $listingId = $listing->sku;
                // primary key from plenty_item_variation_market_status
                $tableId = $listing->id;

                $response = $listingService->getListing( (int) $listingId);

                if ($response['results'][0]['state'] !== "removed") {
                    $deleteResponse = $listingService->deleteListing( (int) $listingId);
                    if (isset($deleteResponse['results']) && is_array($deleteResponse['results']))
                    {
                        $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                        ->addReference('variationId', $variationId)
                        ->error('Listing erfolgreich gelöscht');
                    }
                }

                $variationSkuRepository->delete($tableId);
            }
        } catch (\Exception $exception) {
            $exception->getMessage();
        }
    }
}