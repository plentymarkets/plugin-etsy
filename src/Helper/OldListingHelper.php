<?php
/**
 * Created by IntelliJ IDEA.
 * User: henrymalicha
 * Date: 07.05.19
 * Time: 14:28
 */

namespace Etsy\Helper;


use Etsy\Api\Services\ListingInventoryService;
use Etsy\Api\Services\ListingService;
use Etsy\EtsyServiceProvider;
use Etsy\Services\Item\UpdateListingService;
use Plenty\Modules\Item\SalesPrice\Contracts\SalesPriceNameRepositoryContract;
use Plenty\Modules\Item\SalesPrice\Contracts\SalesPriceRepositoryContract;
use Plenty\Modules\Item\VariationSalesPrice\Contracts\VariationSalesPriceRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Plugin\Log\Loggable;

class OldListingHelper
{

    CONST SALES_PRICE_NAME = "Marktplätze";

    /**
     * number of decimals an counter of money gets rounded to
     */
    const MONEY_DECIMALS = 2;

    use Loggable;

    public function changePrices()
    {
        /** @var VariationSkuRepositoryContract $variationSkuRepository */
        $variationSkuRepository = pluginApp(VariationSkuRepositoryContract::class);
        /** @var ListingInventoryService $listingInventoryService */
        $listingInventoryService = pluginApp(ListingInventoryService::class);
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);
        /** @var  VariationSalesPriceRepositoryContract $variationSalesPriceRepository */
        $variationSalesPriceRepository = pluginApp(VariationSalesPriceRepositoryContract::class);
        /** @var SalesPriceNameRepositoryContract $salesPriceNameRepository */
        $salesPriceNameRepository = pluginApp(SalesPriceNameRepositoryContract::class);
        /** @var SalesPriceRepositoryContract $salesPriceRepository */
        $salesPriceRepository = pluginApp(SalesPriceRepositoryContract::class);

        $finalPriceId = '';

        $results = $salesPriceRepository->all();
        foreach ($results->getResult() as $result) {
            $priceId = $result->id;

            $salesPriceData = $salesPriceNameRepository->findOne($priceId, 'de');

            if ($salesPriceData->nameExternal === self::SALES_PRICE_NAME) {
                $finalPriceId = $priceId;
                break;
            }
        }

        $filter = [
            'marketId' => $settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER)
        ];

        $listings = $variationSkuRepository->search($filter);



            foreach ($listings as $listing) {
                $variationId = $listing->variationId;
                $listingId = $listing->parentSku;

                try {
                    $etsyListing = $listingInventoryService->getInventory($listingId)['results'];

                    $salesPrices = $variationSalesPriceRepository->findByVariationIdWithInheritance($variationId);

                    $finalPrice = "";

                    foreach ($salesPrices as $salesPrice) {
                        if ($salesPrice->salesPriceId === $finalPriceId) {
                            $finalPrice = (float) round($salesPrice->price, self::MONEY_DECIMALS);
                            break;
                        }
                    }

                    $quantity =  $etsyListing['products'][0]['offerings'][0]['quantity'];

                    $etsyListing['products'][0]['offerings'] = [
                        [
                            'quantity' => $quantity,
                            'price' => $finalPrice
                        ]
                    ];

                    $etsyListing['products'] = json_encode($etsyListing['products']);

                    $response = $listingInventoryService->updateInventory($listingId, $etsyListing);

                    if (!isset($response['results']) || !is_array($response['results'])) {
                        throw new \Exception();
                    }

                    $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                        ->addReference('variationId', $variationId)
                        ->error('Price Updated for'.$variationId);





                } catch (\Throwable $exception) {
                    $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                        ->addReference('variationId', $listing->variationId)
                        ->error($exception->getMessage());
                }
            }

}
    public function migrateOldListings() {
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

                if ($currentState['state'] != 'active') {
                    $data['state'] = $currentState['state'] == 'draft' ? 'draft' : 'inactive';
                }

                $response = $listingService->updateListing($listingId, $data);

                if (!isset($response['results']) || !is_array($response['results'])) {
                    throw new \Exception();
                }

                $etsyListing = $listingInventoryService->getInventory($listingId)['results'];
                if (count($etsyListing['products']) > 1) throw new \Exception();

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