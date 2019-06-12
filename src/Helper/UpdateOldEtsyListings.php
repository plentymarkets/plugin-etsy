<?php

namespace Etsy\Helper;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\Api\Services\ListingService;
use Etsy\EtsyServiceProvider;
use Etsy\Services\Item\UpdateListingService;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
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
        /** @var OrderHelper $orderHelper */
        $orderHelper = pluginApp(OrderHelper::class);
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);
        /** @var PropertyRepositoryContract $propertyRepository */
        $propertyRepository = pluginApp(PropertyRepositoryContract::class);
        /** @var PropertyNameRepositoryContract $propertyNameRepository */
        $propertyNameRepository = pluginApp(PropertyNameRepositoryContract::class);
        /** @var PropertyRelationRepositoryContract $propertyRelationRepository */
        $propertyRelationRepository = pluginApp(PropertyRelationRepositoryContract::class);
        /** @var ItemDataLayerRepositoryContract $itemDataLayerRepository */
        $itemDataLayerRepository = pluginApp(ItemDataLayerRepositoryContract::class);

        $doNotExportProperty = [
            'cast' => 'shortText',
            'typeIdentifier' => 'item',
            'position' => 0,
            'names' => [
                [
                    'lang' => 'de',
                    'name' => 'Vom Export ausgeschlossene Variante',
                    'description' => 'Ist diese Eigenschaft hinterlegt, wird die Variante nicht exportiert!'
                ]
            ]
        ];

        $resultFields = [
            'variationBase' => [
                'id'
            ],
            'variationMarketStatus' => [
                'params' => [
                    'marketId' => $orderHelper->getReferrerId()
                ],
                'fields' => [
                    'id',
                    'sku',
                    'marketStatus',
                    'additionalInformation',
                ]
            ]
        ];

        $filters = [
            'variationBase.isActive?' => [],
            'variationVisibility.isVisibleForMarketplace' => [
                'mandatoryOneMarketplace' => [],
                'mandatoryAllMarketplace' => [
                    $orderHelper->getReferrerId()
                ]
            ]
        ];

        $params = [
            'referrerId' => $orderHelper->getReferrerId(),
        ];

        try {
            $itemDataResult = $itemDataLayerRepository->search($resultFields, $filters, $params);

        } catch (\Throwable $e) {
            $e->getMessage();
            $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                ->error('itemDataLayer Error');
        }

        $property = $propertyRepository->createProperty($doNotExportProperty);

        $doNotExportProperty['names'][0]['propertyId'] = $property->id;
        $propertyNameRepository->createName($doNotExportProperty['names'][0]);

        foreach ($itemDataResult->toArray() as $variation) {

            $variationId = $variation->variationBase->id;

            try {
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

                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $variationId)
                    ->error('Eigenschaft verknüpft');
            } catch (\Throwable $exception) {
                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $variationId)
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
        /** @var ListingService $listingService */
        $listingService = pluginApp(ListingService::class);

        $filter = [
            'marketId' => $settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER)
        ];

        $listings = $variationSkuRepository->search($filter);

        $counter = 0;

        foreach ($listings as $listing) {
            $listingId = $listing->sku;
            $variationId = $listing->variationId;

            if (isset($listing['plenty_item_variation_market_status_deleted_timestamp'])) {
                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $variationId)
                    ->addReference('deleted_timestamp', $listing['plenty_item_variation_market_status_deleted_timestamp'])
                    ->error('Variante wurde gelöscht und übersprungen, siehe Referenz');
                continue;
            }

            try {

                $etsyListingState = $listingService->getListing( (int) $listingId);

                if ($etsyListingState['results'][0]['state'] === "removed") {
                    $listing->delete();
                    $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                        ->addReference('variationId', $variationId)
                        ->error('Listing im Status removed, Eintrag im Verfügbarkeitstab und Tabelle gelöscht');
                    continue;
                }

                if ($etsyListingState['results'][0]['state'] === "edit") {
                    $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                        ->addReference('variationId', $variationId)
                        ->error('Listing was in state sold_out');
                    continue;
                }

                if ($etsyListingState['results'][0]['state'] === "sold_out") {
                    $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                        ->addReference('variationId', $variationId)
                        ->error('Listing was in state sold_out');
                    continue;
                }

                if ($etsyListingState['results'][0]['state'] === "expired") {
                    $etsyListing = $listingInventoryService->getInventory($listingId)['results'];

                    $etsyListing['products'][0]['sku'] = $listingId . '-' . $variationId;
                    if ($etsyListing['products'][0]['offerings'][0]['quantity']) {
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
                    }

                    $etsyListing['products'][0]['offerings'] = [
                        [
                            'quantity' => $quantity,
                            'price' => $price
                        ]
                    ];

                    $etsyListing['products'] = json_encode($etsyListing['products']);

                    try {
                        $response = $listingInventoryService->updateInventory($listingId, $etsyListing);
                    } catch (\Throwable $exception) {
                        $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                            ->addReference('variationId', $listing->variationId)
                            ->error('Failed to update Inventory');
                    }

                    if (isset($response['results']) || !is_array($response['results'])) {
                        $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                            ->addReference('variation', $variationId)
                            ->error('Listing Updated');
                    }

                    $listing->sku = $listingId . '-' . $variationId;
                    $listing->status = ItemHelper::SKU_STATUS_INACTIVE;
                    $listing->parentSku = $listingId;
                    $listing->save();
                }

                if ($etsyListingState['results'][0]['state'] === "active") {
                    $etsyListing = $listingInventoryService->getInventory($listingId)['results'];

                    $etsyListing['products'][0]['sku'] = $listingId . '-' . $variationId;
                    if ($etsyListing['products'][0]['offerings'][0]['quantity']) {
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
                    }

                    $etsyListing['products'][0]['offerings'] = [
                        [
                            'quantity' => $quantity,
                            'price' => $price
                        ]
                    ];

                    $etsyListing['products'] = json_encode($etsyListing['products']);

                    try {
                        $response = $listingInventoryService->updateInventory($listingId, $etsyListing);
                    } catch (\Throwable $exception) {
                        $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                            ->addReference('variationId', $listing->variationId)
                            ->error('Failed to update Inventory');
                    }

                    if (isset($response['results']) || !is_array($response['results'])) {
                        $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                            ->addReference('variation', $variationId)
                            ->error('Listing Updated');
                    }

                    $listing->sku = $listingId . '-' . $variationId;
                    $listing->status = ItemHelper::SKU_STATUS_ACTIVE;
                    $listing->parentSku = $listingId;
                    $listing->save();
                }
            } catch (\Throwable $exception) {
                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $listing->variationId)
                    ->error('Migration failed');
            }
            $counter++;

            if ($counter >= 5) {
                break;
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