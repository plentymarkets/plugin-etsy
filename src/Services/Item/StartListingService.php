<?php

namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\Validators\EtsyListingValidator;
use Illuminate\Database\Eloquent\Collection;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Etsy\Helper\ImageHelper;
use Etsy\Helper\SettingsHelper;
use Etsy\Api\Services\ListingService;
use Etsy\Api\Services\ListingImageService;
use Etsy\Helper\ItemHelper;
use Etsy\Api\Services\ListingTranslationService;
use Plenty\Modules\Item\ItemShippingProfiles\Contracts\ItemShippingProfilesRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationExportServiceContract;
use Plenty\Modules\Item\Variation\Services\ExportPreloadValue\ExportPreloadValue;
use Plenty\Modules\StockManagement\Stock\Repositories\StockRepository;
use Plenty\Modules\System\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\Log\Loggable;

/**
 * Class StartListingService
 */
class StartListingService
{
    use Loggable;

    /**
     * @var ItemHelper
     */
    private $itemHelper;

    /**
     * @var ListingService
     */
    private $listingService;

    /**
     * @var DeleteListingService
     */
    private $deleteListingService;

    /**
     * @var ListingImageService
     */
    private $listingImageService;

    /**
     * @var ListingTranslationService
     */
    private $listingTranslationService;

    /**
     * @var SettingsHelper
     */
    private $settingsHelper;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var ListingInventoryService $inventoryService
     */
    private $inventoryService;

    /**
     * @var $variationExportService
     */
    private $variationExportService;

    /**
     * String values which can be used in properties to represent true
     */
    const BOOL_CONVERTIBLE_STRINGS = ['1', 'y', 'true'];

    /**
     * StartListingService constructor.
     * @param ListingService $listingService
     * @param ItemHelper $itemHelper
     * @param DeleteListingService $deleteListingService
     * @param ListingImageService $listingImageService
     * @param ListingTranslationService $listingTranslationService
     * @param SettingsHelper $settingsHelper
     * @param ImageHelper $imageHelper
     * @param ListingInventoryService $inventoryService
     * @param VariationExportServiceContract $variationExportService
     * @internal param StockRepository $stockRepository
     */
    public function __construct(
        ListingService $listingService,
        ItemHelper $itemHelper,
        DeleteListingService $deleteListingService,
        ListingImageService $listingImageService,
        ListingTranslationService $listingTranslationService,
        SettingsHelper $settingsHelper,
        ImageHelper $imageHelper,
        ListingInventoryService $inventoryService,
        VariationExportServiceContract $variationExportService
    )
    {
        $this->itemHelper = $itemHelper;
        $this->listingTranslationService = $listingTranslationService;
        $this->listingService = $listingService;
        $this->deleteListingService = $deleteListingService;
        $this->listingImageService = $listingImageService;
        $this->settingsHelper = $settingsHelper;
        $this->imageHelper = $imageHelper;
        $this->inventoryService = $inventoryService;
        $this->variationExportService = $variationExportService;
    }

    /**
     * Start the listing
     *
     * @param array $listing
     */
    public function start(array $listing)
    {
        if (isset($listing['main'])) {
            $listingId = $this->createListing($listing);

            try {

                //todo: translations
                $this->fillInventory($listingId, $listing);
                //$this->addPictures($listingId, $listing);

                throw new \Exception(); //todo: entfernen wenn fertig
                $this->publish($listingId, $listing);
            } catch (\Exception $e) {
                $this->itemHelper->deleteListingsSkus($listingId, $this->settingsHelper->get($this->settingsHelper::SETTINGS_ORDER_REFERRER));
                $this->listingService->deleteListing($listingId);
            }


            /*
            $listingId = $this->createListing($record);

            if(!is_null($listingId))
            {u
                try
                {
                    $this->addPictures($record, $listingId);

                    $this->addTranslations($record, $listingId);

                    $this->publish($listingId, $record->variationBase->id);

                    $this->getLogger(__FUNCTION__)
                         ->addReference('etsyListingId', $listingId)
                         ->addReference('variationId', $record->variationBase->id)
                         ->report('Etsy::item.itemExportSuccess');
                }
                catch(\Exception $ex)
                {
                    $this->deleteListingService->delete($listingId);

                    $this->getLogger(__FUNCTION__)
                         ->addReference('variationId', $record->variationBase->id)
                         ->addReference('etsyListingId', $listingId)
                         ->warning('Etsy::item.skuRemovalSuccess', [
                             'sku' => $record->variationMarketStatus->sku
                         ]);

                    $this->getLogger(__FUNCTION__)
                        ->addReference('variationId', $record->variationBase->id)
                        ->addReference('etsyListingId', $listingId)
                        ->error('Etsy::item.startListingError', $ex->getMessage());
                }
            }
            else
            {
                $this->getLogger(__FUNCTION__)
                    ->setReferenceType('variationId')
                    ->setReferenceValue($record->variationBase->id)
                    ->info('Etsy::item.startListingError');
            }
            */
        }
    }

    /**
     * Create a listing base.
     *
     * @param array $listing
     *
     * @throws \Exception
     * @return int
     */
    private function createListing(array $listing)
    {
        $data = [];
        $failedVariations = [];
        $variationExportService = $this->variationExportService;
        EtsyListingValidator::validateOrFail($listing['main']);


        $data['state'] = 'draft';

        $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');

        //title and description
        foreach ($listing['main']['texts'] as $text) {
            if ($text['lang'] == $language) {
                $data['title'] = str_replace(':', ' -', $text['name1']);
                $data['title'] = ltrim($data['title'], ' +-!?');

                $data['description'] = html_entity_decode(strip_tags($text['description']));
            }
        }

        //quantity & price
        $data['quantity'] = 0;
        $hasActiveVariations = false;

        $variationExportService->addPreloadTypes([$this->variationExportService::STOCK]);
        $exportPreloadValueList = [];

        foreach ($listing as $variation) {
            $exportPreloadValue = pluginApp(ExportPreloadValue::class, [
                'itemId' => $variation['itemId'],
                'variationId' => $variation['variationId']
            ]);

            $exportPreloadValueList[] = $exportPreloadValue;
        }

        foreach ($listing as $key => $variation) {
            if (!$variation['isActive']) {
                continue;
            }

            $variationExportService->preload($exportPreloadValueList);
            $stock = $variationExportService->getAll($variation['variationId']);
            $stock = $stock[$variationExportService::STOCK];

            if (!isset($variation['sales_price']) || !isset($stock) || $stock[0]['stockNet'] < 1) {
                unset($listing[$key]);
                $failedVariations[] = $variation;
            }

            $data['quantity'] += $stock[0]['stockNet'];

            if (!isset($data['price']) || $data['price'] > $variation['sales_price']) {
                $data['price'] = $variation['sales_price'];
            }

            $hasActiveVariations = true;
        }

        //was ist mit mehreren Versandprofilen?? todo
        $data['shipping_template_id'] = $listing['main']['shipping_profiles'][0];

        $data['who_made'] = $listing['main']['who_made'];
        $data['is_supply'] = (in_array(strtolower($listing['main']['is_supply']),
            self::BOOL_CONVERTIBLE_STRINGS));
        $data['when_made'] = $listing['main']['when_made'];

        //Kategorie todo: umbauen auf Standardkategorie
        //$data['taxonomy_id'] = $listing['main']['categories'][0];
        $data['taxonomy_id'] = 1069;

        if (false) {
            //todo: Still need to decide how to map tags for Etsy (plenty tags from main variation or maybe properties?)
            $data['tags'] = '';
        }

        if (isset($listing['main']['occasion'])) {
            $data['occasion'] = $listing['main']['occasion'];
        }

        if (isset($listing['main']['recipient'])) {
            $data['recipient'] = $listing['main']['recipient'];
        }

        if (isset($listing['main']['item_weight'])) {
            $data['item_weight'] = $listing['main']['item_weight'];
            $data['item_weight_units'] = 'g';
        }

        if (isset($listing['main']['item_height'])) {
            $data['item_height'] = $listing['main']['item_height'];
            $data['item_dimensions_unit'] = 'mm';
        }

        if (isset($listing['main']['item_length'])) {
            $data['item_length'] = $listing['main']['item_length'];
            $data['item_dimensions_unit'] = 'mm';
        }

        if (isset($listing['main']['item_width'])) {
            $data['item_width'] = $listing['main']['item_width'];
            $data['item_dimensions_unit'] = 'mm';
        }

        if (isset($listing['main']['materials'])) {
            $data['materials'] = explode(',', $listing['main']['materials']);
        }

        if (isset($listing['main']['is_customizable'])) {
            $data['is_customizable'] = (in_array(strtolower($listing['main']['is_customizable']),
                self::BOOL_CONVERTIBLE_STRINGS));
        }

        if (isset($listing['main']['non_taxable'])) {
            $data['non_taxable'] = (in_array(strtolower($listing['main']['non_taxable']),
                self::BOOL_CONVERTIBLE_STRINGS));
        }

        if (isset($listing['main']['processing_min'])) {
            $data['processing_min'] = $listing['main']['processing_min'];
        }

        if (isset($listing['main']['processing_max'])) {
            $data['processing_max'] = $listing['main']['processing_max'];
        }

        if (isset($listing['main']['style']) && is_array($listing['main']['style'])) {
            foreach($listing['main']['style'] as $style) {
                if (preg_match('@[^\p{L}\p{Nd}\p{Zs}l]u', $style)) {
                    //todo log
                    continue;
                }

                $data['style'][] = $style;
            }
        }

        if (isset($listing['main']['shop_section_id'])) {
            $data['shop_section_id'] = $listing['main']['shop_section_id'];
        }

        if (!$hasActiveVariations) {
            throw new \Exception('Failed to list item with id ' . $listing['main']['itemId'] .
                '. No active variations with positive stock.');
        }

        if ((!isset($data['title']) || $data['title'] == '')
        ||  (!isset($data['description']) || $data['description'] == '')) {
            throw new \Exception('Failed to list item with id ' . $listing['main']['itemId'] .
                '. Title and description required');
        }

        if (strlen($data['title']) > 140) {
            throw new \Exception('Failed to list item with id ' . $listing['main']['itemId'] .
                '. Title can not be longer than 140 characters');
        }

        $response = $this->listingService->createListing($language, $data);


        if (!isset($response['results']) || !is_array($response['results'])) {
            if (is_array($response) && isset($response['error_msg'])) {
                $message = $response['error_msg'];
            } else {
                if (is_string($response)) {
                    $message = $response;
                } else {
                    $message = 'Failed to create listing.';
                }
            }

            throw new \Exception($message);
        }

        //todo: nicht listbare varianten loggen

        $results = (array)$response['results'];

        return (int)reset($results)['listing_id'];
    }

    /**
     * Creates variations for the listing
     *
     * @param $listingId
     * @param $listing
     * @throws \Exception
     */
    private function fillInventory($listingId, $listing)
    {
        $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');
        $products = [];
        $dependencies = [];

        if (count($listing['main']['attributes']) > 2) {
            throw new \Exception("Can't list article " . $listing['main']['itemId'] . ". Too many attributes.");
        }

        if (isset($listing['main']['attributes'][0])) {
            $attributeOneId = $listing['main']['attributes'][0]['attributeId'];
            $dependencies[] = $this->inventoryService::CUSTOM_ATTRIBUTE_1;
        }

        if (isset($listing['main']['attributes'][1])) {
            $attributeTwoId = $listing['main']['attributes'][1]['attributeId'];
            $dependencies[] = $this->inventoryService::CUSTOM_ATTRIBUTE_2;
        }

        $counter = 0;

        foreach ($listing as $variation) {

            //initialising property values array for articles with no attributes (single variation)
            $products[$counter]['property_values'] = [];
            /*
                        $this->stockRepository->setFilters(['variationId' => $variation['variationId']]);
                        $stock = $this->stockRepository->listStockByWarehouseType('sales')->getResult()->first();

                        if ($stock->stockNet === NULL || !$variation['isActive'])
                        {
                            continue;
                        }
            */
            foreach ($variation['attributes'] as $attribute) {

                foreach ($attribute['attribute']['names'] as $name) {
                    if ($name['lang'] == $language) {
                        $attributeName = $name['name'];
                    }
                }

                foreach ($attribute['value']['names'] as $name) {
                    if ($name['lang'] == $language) {
                        $attributeValueName = $name['name'];
                    }
                }

                if (!isset($attributeName)) {
                    throw new \Exception("Can't list variation " . $variation['variationId'] . ". Undefined attribute name for language " . $language . ".");
                }

                if (!isset($attributeValueName)) {
                    throw new \Exception("Can't list variation " . $variation['variationId'] . ". Undefined attribute value name for language " . $language . ".");
                }

                if (isset($attributeOneId) && $attribute['attributeId'] == $attributeOneId) {
                    $products[$counter]['property_values'][] = [
                        'property_id' => $this->inventoryService::CUSTOM_ATTRIBUTE_1,
                        'property_name' => $attributeName,
                        'values' => [$attributeValueName],
                    ];
                } elseif (isset($attributeTwoId) && $attribute['attributeId'] == $attributeTwoId) {
                    $products[$counter]['property_values'][] = [
                        'property_id' => $this->inventoryService::CUSTOM_ATTRIBUTE_2,
                        'property_name' => $attributeName,
                        'values' => [$attributeValueName],
                    ];
                }
            }

            $orderReferrer = $this->settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER);
            foreach ($variation['salesPrices'] as $salesPrice) {

                if (in_array($orderReferrer, $salesPrice['settings']['referrers'])) {
                    $price = $salesPrice['price'];
                    break;
                }
            }

            //Creating a formatted array so the method can use the data
            $products[$counter]['sku'] = $this->itemHelper->generateParentSku($listingId, [
                'id' => $variation['variationId'],
                'data' => [
                    'item' => [
                        'id' => $variation['itemId']
                    ]
                ]
            ]);

            $products[$counter]['offerings'] = [
                [
                    'quantity' => 1,
                    'is_enabled' => $variation['isActive']
                ]
            ];

            if (isset($price)) {
                $products[$counter]['offerings'][0]['price'] = $price;
            }

            $counter++;
        }

        if ($counter == 0) {
            throw new \Exception("Can't list article " . $listing['main']['itemId'] . ". No active variations");
        }

        $data = [
            'products' => json_encode($products),
            'price_on_property' => $dependencies,
            'quantity_on_property' => $dependencies,
            'sku_on_property' => $dependencies
        ];

        $this->inventoryService->updateInventory($listingId, $data, $language);

    }

    /**
     * Add pictures to listing.
     *
     * @param int $listingId
     * @param $listing
     */
    private function addPictures($listingId, $listing)
    {
        $list = $listing['main']['images']['all'];

        $list = $this->imageHelper->sortImagePosition($list);

        $imageList = [];

        $list = array_slice($list, 0, 10);

        foreach ($list as $image) {

            if ($image['availabilities']['market'] !== -1 && $image['availabilities']['market'] !== $this->settingsHelper->get($this->settingsHelper::SETTINGS_ORDER_REFERRER)) {
                continue;
            }

            $response = $this->listingImageService->uploadListingImage($listingId, $image['url'], $image['position']);


            if (isset($response['results']) && isset($response['results'][0]) && isset($response['results'][0]['listing_image_id'])) {
                $imageList[] = [
                    'imageId' => $image['id'],
                    'listingImageId' => $response['results'][0]['listing_image_id'],
                    'listingId' => $response['results'][0]['listing_id'],
                    'imageUrl' => $image['url']
                ];

            }
            break;
        }

        if (count($imageList)) {
            $this->imageHelper->save($listing['main']['variationId'], json_encode($imageList));
        }

    }

    /**
     * Add translations to listing.
     *
     * @param Record $record
     * @param int $listingId
     */
    private function addTranslations(Record $record, $listingId)
    {
        foreach ($this->settingsHelper->getShopSettings('exportLanguages',
            [$this->settingsHelper->getShopSettings('mainLanguage', 'de')]) as $language) {
            if ($language != $this->settingsHelper->getShopSettings('mainLanguage',
                    'de') && $record->itemDescription[$language]['name1'] && strip_tags($record->itemDescription[$language]['description'])) {
                try {
                    $title = trim(preg_replace('/\s+/', ' ', $record->itemDescription[$language]['name1']));
                    $title = ltrim($title, ' +-!?');

                    $legalInformation = $this->itemHelper->getLegalInformation($language);

                    $data = [
                        'title' => $title,
                        'description' => html_entity_decode(strip_tags($record->itemDescription[$language]['description'] . $legalInformation)),
                    ];

                    if ($record->itemDescription[$language]['keywords']) {
                        $data['tags'] = $this->itemHelper->getTags($record, $language);
                    }

                    $this->listingTranslationService->createListingTranslation($listingId, $language, $data);
                } catch (\Exception $ex) {
                    $this->getLogger(__FUNCTION__)
                        ->addReference('etsyListingId', $listingId)
                        ->addReference('variationId', $record->variationBase->id)
                        ->addReference('etsyLanguage', $language)
                        ->error('Etsy::item.translationUpdateError', $ex->getMessage());
                }
            }
        }
    }

    /**
     * @param int $listingId
     * @param int $variationId
     */
    private function publish($listingId, $listing)
    {
//        $data = [
//            'state' => 'active',
//        ];
//
//        $this->listingService->updateListing($listingId, $data);

        //todo: skus aktiv schalten
    }
}
