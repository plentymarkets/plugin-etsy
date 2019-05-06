<?php

namespace Etsy\Migrations;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\Api\Services\ListingService;
use Etsy\EtsyServiceProvider;
use Etsy\Helper\ImageHelper;
use Etsy\Helper\ItemHelper;
use Etsy\Helper\SettingsHelper;
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

    public function run()
    {
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);
        /** @var VariationSkuRepositoryContract $variationSkuRepository */
        $variationSkuRepository = pluginApp(VariationSkuRepositoryContract::class);
        /** @var ListingService $listingService */
        $listingService = pluginApp(ListingService::class);
        /** @var ListingInventoryService $listingInventoryService */
        $listingInventoryService = pluginApp(ListingInventoryService::class);
        /** @var ImageHelper  $imageHelper */
        $imageHelper = pluginApp(ImageHelper::class);
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

        /** @var VariationSku $listing */
        foreach ($listings as $listing) {
            $listingId = $listing->sku;
            $variationId = $listing->variationId;

            try {
                if (isset($listing['plenty_item_variation_market_status_deleted_timestamp'])) continue;

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

                $etsyListing = $listingInventoryService->getInventory($listingId)['results'];
                $etsyListing['products'][0]['sku'] = $listingId . '-' . $variationId;
                $data['should_auto_renew'] = false;
                $data['write_missing_inventory'] = true;

                if ($etsyListing['products'][0]['offerings'][0]['quantity']) {
                    $data['state'] = 'inactive';
                    $listing->status = ItemHelper::SKU_STATUS_INACTIVE;
                    $etsyListing['products'][0]['offerings'][0]['quantity'] = 1;
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
                $listing->sku = $listingId . '-' . $variationId;
                $listing->parentSku = $listingId;
                $listing->save();

                $images = $imageHelper->get($listing->variationId);

                /** @var VariationRepositoryContract $variationRepository */
                $variationRepository = pluginApp(VariationRepositoryContract::class);
                $variation = $variationRepository->show($variationId, [], 'de');

                $position = 1;
                $newImages = [];

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

            }  catch (\Throwable $exception) {
                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $listing->variationId)
                    ->error('Migration failed');
            }
        }
    }
}