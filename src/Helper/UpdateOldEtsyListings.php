<?php

namespace Etsy\Helper;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\Api\Services\ListingService;
use Etsy\EtsyServiceProvider;
use Etsy\Services\Item\UpdateListingService;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Modules\Property\Contracts\PropertyNameRepositoryContract;
use Plenty\Modules\Property\Contracts\PropertyRelationRepositoryContract;
use Plenty\Modules\Property\Contracts\PropertyRepositoryContract;
use Plenty\Plugin\Log\Loggable;


class UpdateOldEtsyListings
{
    use Loggable;

    /**
     * Creates the do not export property which is used to exclude items from the export
     */
    public function createAndAddPropertyToAllEtsyItems()
    {
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);
        /** @var VariationSkuRepositoryContract $variationSkuRepository */
        $variationSkuRepository = pluginApp(VariationSkuRepositoryContract::class);
        /** @var PropertyRepositoryContract $propertyRepository */
        $propertyRepository = pluginApp(PropertyRepositoryContract::class);
        /** @var PropertyNameRepositoryContract $propertyNameRepository */
        $propertyNameRepository = pluginApp(PropertyNameRepositoryContract::class);
        /** @var PropertyRelationRepositoryContract $propertyRelationRepository */
        $propertyRelationRepository = pluginApp(PropertyRelationRepositoryContract::class);

        $filter = [
            'marketId' => $settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER)
        ];

        $listings = $variationSkuRepository->search($filter);

        $doNotExportProperty = [
            'cast' => 'shortText',
            'typeIdentifier' => 'item',
            'position' => 0,
            'names' => [
                [
                    'lang' => 'de',
                    'name' => 'Vom Export ausgeschlossen',
                    'description' => 'Ist diese Eigenschaft hinterlegt, wird der Artikel nicht exportiert!'
                ]
            ]
        ];

        $property = $propertyRepository->createProperty($doNotExportProperty);

        $doNotExportProperty['names'][0]['propertyId'] = $property->id;
        $propertyNameRepository->createName($doNotExportProperty['names'][0]);

        foreach ($listings as $listing) {
            $variationId = $listing->variationId;

            try {
                if (isset($listing['plenty_item_variation_market_status_deleted_timestamp'])) {
                    continue;
                }

                $propertyRelationRepository->createRelation([
                    'relationTargetId' => $variationId,
                    'propertyId' => $property->id,
                    'relationTypeIdentifier' => 'item',
                    'relationValues' => [
                        [
                            'lang' => 'de',
                            'value' => 'true',
                            'description' => ''
                        ]
                    ]
                ]);
            } catch (\Throwable $exception) {
                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $listing->variationId)
                    ->error('Migration failed');
            }
        }
    }

    /**
     * Changes sku *etsyListingId* to *etsyListingId-plentyVariationId* in the plenty_item_variation_market_status table
     * and in the etsy shop
     */
    public function replaceOldSkuWithNewSku()
    {
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);
        /** @var VariationSkuRepositoryContract $variationSkuRepository */
        $variationSkuRepository = pluginApp(VariationSkuRepositoryContract::class);
        /** @var ListingInventoryService $listingInventoryService */
        $listingInventoryService = pluginApp(ListingInventoryService::class);

        $filter = [
            'marketId' => $settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER)
        ];

        $listings = $variationSkuRepository->search($filter);

        foreach ($listings as $listing) {
            $listingId = $listing->sku;
            $variationId = $listing->variationId;

            try {

                $etsyListing = $listingInventoryService->getInventory($listingId)['results'];
                if (count($etsyListing['products']) > 1) {
                    throw new \Exception();
                }

                $etsyListing['products'][0]['sku'] = $listingId . '-' . $variationId;
                if ($etsyListing['products'][0]['offerings'][0]['quantity']) {
                    $data['state'] = 'inactive';
                    $quantity = $etsyListing['products'][0]['offerings'][0]['quantity'];

                    if (!isset($etsyListing['products'][0]['offerings'][0]['price']['before_conversion'])) {
                        $price = (float)($etsyListing['products'][0]['offerings'][0]['price']['amount'] /
                            $etsyListing['products'][0]['offerings'][0]['price']['divisor']);
                        $price = round($price, UpdateListingService::MONEY_DECIMALS);
                    } else {
                        $price = (float)($etsyListing['products'][0]['offerings'][0]['price']['before_conversion']['amount'] /
                            $etsyListing['products'][0]['offerings'][0]['price']['before_conversion']['divisor']);
                        $price = round($price, UpdateListingService::MONEY_DECIMALS);
                    }

                    if ($etsyListing['products'][0]['offerings'][0]['quantity'] < 1) {
                        $listing->status = ItemHelper::SKU_STATUS_INACTIVE;
                        $etsyListing['products'][0]['offerings'][0]['quantity'] = 1;
                        $quantity = 1;
                    }
                }
                $etsyListing['products'][0]['offerings'] = [
                    [
                        'quantity' => $quantity,
                        'price' => $price
                    ]
                ];

                $etsyListing['products'] = json_encode($etsyListing['products']);
                try {
                    if (isset($listing['plenty_item_variation_market_status_deleted_timestamp'])) {
                        continue;
                    }

                    $response = $listingInventoryService->updateInventory($listingId, $etsyListing);
                } catch (\Throwable $exception) {
                    $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                        ->addReference('variationId', $listing->variationId);

                }


                if (!isset($response['results']) || !is_array($response['results'])) {
                    throw new \Exception();
                }

                $listing->sku = $listingId . '-' . $variationId;
                $listing->parentSku = $listingId;
                $listing->save();

            } catch (\Throwable $exception) {
                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $listing->variationId)
                    ->error('Migration failed');
            }
        }
    }

    public function updateImageData()
    {
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);
        /** @var VariationSkuRepositoryContract $variationSkuRepository */
        $variationSkuRepository = pluginApp(VariationSkuRepositoryContract::class);
        /** @var ImageHelper $imageHelper */
        $imageHelper = pluginApp(ImageHelper::class);

        $filter = [
            'marketId' => $settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER)
        ];

        $listings = $variationSkuRepository->search($filter);

        foreach ($listings as $listing) {
            $listingId = $listing->sku;
            $variationId = $listing->variationId;

            $images = $imageHelper->get($listing->variationId);

            /** @var VariationRepositoryContract $variationRepository */
            $variationRepository = pluginApp(VariationRepositoryContract::class);
            $variation = $variationRepository->show($variationId, [], 'de');

            $position = 1;
            $newImages = [];

            try {
                foreach ($images as $image) {
                    $newImages[] = [
                        'imageId' => $image['id'],
                        'listingImageId' => $image['listingImageId'],
                        'listingId' => $listingId,
                        'itemId' => $variation->itemId,
                        'position' => $position++,
                        'imageUrl' => $image['url']
                    ];
                }

                $imageHelper->save($listingId, json_encode($newImages));
                $imageHelper->delete($variationId);
            } catch (\Throwable $exception) {
                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $listing->variationId)
                    ->error('Migration failed');
            }
        }

    }
}