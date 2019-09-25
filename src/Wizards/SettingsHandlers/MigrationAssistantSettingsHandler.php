<?php

namespace Etsy\Wizards\SettingsHandlers;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\Api\Services\ListingService;
use Etsy\EtsyServiceProvider;
use Etsy\Helper\ImageHelper;
use Etsy\Helper\ItemHelper;
use Etsy\Helper\OrderHelper;
use Etsy\Helper\SettingsHelper;
use Etsy\Services\Item\UpdateListingService;
use Etsy\Wizards\MigrationAssistant;
use Plenty\Modules\Cloud\DynamoDb\Repositories\DynamoDbRepository;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;
use Plenty\Modules\Property\Contracts\PropertyNameRepositoryContract;
use Plenty\Modules\Property\Contracts\PropertyRelationRepositoryContract;
use Plenty\Modules\Property\Contracts\PropertyRepositoryContract;
use Plenty\Modules\Wizard\Contracts\WizardSettingsHandler as AssistantSettingsHandler;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;

class MigrationAssistantSettingsHandler implements AssistantSettingsHandler
{
    use Loggable;

    /**
     * Handle wizard data for a finalized wizard
     *
     * @param array $parameters
     * @return bool
     */
    public function handle(array $parameters): bool
    {
        /** @var DynamoDbRepositoryContract $dynamoDBRepository */
        $dynamoDBRepository = pluginApp(DynamoDbRepositoryContract::class);

        $data = $dynamoDBRepository->getItem(SettingsHelper::PLUGIN_NAME, MigrationAssistant::TABLE_NAME, true, [
            'name' => [DynamoDbRepositoryContract::FIELD_TYPE_BOOL => "isRun"]
        ]);
        $isRun = $data["value"]["BOOL"];
        if (!$isRun) {
            $checkboxValue = $parameters["data"]["checkbox"];

            if (is_bool($checkboxValue)) {
                $this->deleteImageData();
                $this->replaceOldSkuWithNewSku();
                if ($checkboxValue) {
                    $this->createAndAddPropertyToAllEtsyItems();

                }
                $dynamoDBRepository->putItem(SettingsHelper::PLUGIN_NAME, MigrationAssistant::TABLE_NAME, [
                    'name' => [
                        DynamoDbRepositoryContract::FIELD_TYPE_STRING => (string)"isRun",
                    ],
                    'value' => [
                        DynamoDbRepositoryContract::FIELD_TYPE_BOOL => (bool)true,
                    ],
                ]);

                return true;
            } else {
                return false;
            }
        }

        return true;

    }

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
                $etsyListingState = $listingService->getListing((int)$listingId);
                if ($etsyListingState['results'][0]['state'] === "removed") {
                    $listing->delete();
                    $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                        ->addReference('variationId', $variationId)
                        ->error('Listing im Status removed, Eintrag im Verfügbarkeitstab und Tabelle gelöscht');
                    continue;
                }
                if ($etsyListingState['results'][0]['state'] === "edit") {
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
                            ->addReference('variationId', $variationId)
                            ->error('Failed to update Inventory');
                    }
                    if (isset($response['results']) || !is_array($response['results'])) {
                        $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                            ->addReference('variationId', $variationId)
                            ->error('Listing im Status abgelaufen Updated');
                    }
                    $listing->sku = $listingId . '-' . $variationId;
                    $listing->status = ItemHelper::SKU_STATUS_INACTIVE;
                    $listing->parentSku = $listingId;
                    $listing->save();
                }
                if ($etsyListingState['results'][0]['state'] === "sold_out") {
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
                            ->addReference('variationId', $variationId)
                            ->error('Failed to update Inventory');
                    }
                    if (isset($response['results']) || !is_array($response['results'])) {
                        $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                            ->addReference('variationId', $variationId)
                            ->error('Listing im Status abgelaufen Updated');
                    }
                    $listing->sku = $listingId . '-' . $variationId;
                    $listing->status = ItemHelper::SKU_STATUS_INACTIVE;
                    $listing->parentSku = $listingId;
                    $listing->save();
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
                            ->addReference('variationId', $variationId)
                            ->error('Failed to update Inventory');
                    }
                    if (isset($response['results']) || !is_array($response['results'])) {
                        $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                            ->addReference('variationId', $variationId)
                            ->error('Listing im Status abgelaufen Updated');
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
                            ->addReference('variationId', $variationId)
                            ->error('Failed to update Inventory');
                    }
                    if (isset($response['results']) || !is_array($response['results'])) {
                        $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                            ->addReference('variationId', $variationId)
                            ->error('Listing im Status aktiv Updated');
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
        }
    }

    public function deleteImageData()
    {
        /** @var DynamoDbRepositoryContract $dynamoRepo */
        $dynamoRepo = pluginApp(DynamoDbRepositoryContract::class);
        /** @var ConfigRepository $config */
        $config = pluginApp(ConfigRepository::class);
        $dynamoRepo->deleteTable(EtsyServiceProvider::PLUGIN_NAME, 'variation_images');
        $dynamoRepo->createTable(SettingsHelper::PLUGIN_NAME, ImageHelper::TABLE_NAME, [
            [
                'AttributeName' => 'id',
                'AttributeType' => DynamoDbRepositoryContract::FIELD_TYPE_STRING
            ],
        ], [
            [
                'AttributeName' => 'id',
                'KeyType' => 'HASH',
            ],
        ], (int)$config->get(SettingsHelper::PLUGIN_NAME . '.readCapacityUnits', 3),
            (int)$config->get(SettingsHelper::PLUGIN_NAME . '.readCapacityUnits', 2));
    }

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
                    'marketId' => $settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER)
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
}