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
     * @var StockRepository
     */
    private $stockRepository;

    /**
     * @param ItemHelper $itemHelper
     * @param ListingService $listingService
     * @param DeleteListingService $deleteListingService
     * @param ListingImageService $listingImageService
     * @param ListingTranslationService $listingTranslationService
     * @param SettingsHelper $settingsHelper
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        ItemHelper $itemHelper,
        ListingService $listingService,
        DeleteListingService $deleteListingService,
        ListingImageService $listingImageService,
        ListingTranslationService $listingTranslationService,
        SettingsHelper $settingsHelper,
        ImageHelper $imageHelper,
        ListingInventoryService $inventoryService,
        StockRepository $stockRepository)
    {
        $this->itemHelper = $itemHelper;
        $this->listingTranslationService = $listingTranslationService;
        $this->listingService = $listingService;
        $this->deleteListingService = $deleteListingService;
        $this->listingImageService = $listingImageService;
        $this->settingsHelper = $settingsHelper;
        $this->imageHelper = $imageHelper;
        $this->inventoryService = $inventoryService;
        $this->stockRepository = $stockRepository;
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
                $this->addPictures($listingId, $listing);
            } catch (\Exception $e) {
                $this->itemHelper->deleteListingsSkus($listingId, $this->settingsHelper->get($this->settingsHelper::SETTINGS_ORDER_REFERRER));
                $this->listingService->deleteListing($listingId);
            }

            $this->publish($listingId, $listing);
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

        foreach ($listing as $variation) {
            if (!$variation['isActive']) {
                continue;
            }

            $this->stockRepository->setFilters(['variationId' => $variation['variationId']]);
            $stock = $this->stockRepository->listStockByWarehouseType('sales')->getResult()->first();

            if ($stock->stockNet === NULL)
            {
                continue;
            }

            $hasActiveVariations = true;

            $data['quantity'] += $stock->stockNet;

            //loading default currency
            /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
            $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
            $webStoreConfiguration = $webstoreConfigurationRepository->findByPlentyId(pluginApp(Application::class)->getPlentyId());
            $defaultCurrency = $webStoreConfiguration->defaultCurrency;

            //todo: Nur den in den Einstellungen definierten Preis für Etsy nutzen und auf Shopwährung prüfen. Ggf. umrechnen
            foreach ($variation['salesPrices'] as $salesPrice) {
                $orderReferrer = $this->settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER);

                //todo Falls die bei Etsy hinterlegte Währung von der Standardwährung abweicht muss umgerechnet werden
                if (in_array($orderReferrer, $salesPrice['settings']['referrers'])) {
                    if (!isset($data['price']) || $salesPrice['price'] < $data['price']) {
                        $data['price'] =  (float) $salesPrice['price'];
                    }
                    break;
                }
            }
        }

        $boolConvertableStrings = ['1', 'y', 'true'];

        //was ist mit mehreren Versandprofilen?? todo
        $data['shipping_template_id'] = $listing['main']['shipping_profiles'][0];

        //who_made -> gemappte eigenschaft des kunden
        $data['who_made'] = $listing['main']['who_made'];
        //is_supply ->
        $data['is_supply'] = (in_array(strtolower($listing['main']['is_supply']), $boolConvertableStrings)) ? true : false;
        //when_made -> ^
        $data['when_made'] = $listing['main']['when_made'];

        //Kategorie todo: umbauen auf Standardkategorie
        $data['taxonomy_id'] = $listing['main']['categories'][0];


        //Adding fields to data array if they are mapped and have a value
        if (false) {
            //todo
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
            $data['is_customizable'] = (in_array(strtolower($listing['main']['is_customizable']), $boolConvertableStrings)) ? true : false;
        }

        if (isset($listing['main']['non_taxable'])) {
            $data['non_taxable'] = (in_array(strtolower($listing['main']['non_taxable']), $boolConvertableStrings)) ? true : false;
        }

        if (isset($listing['main']['processing_min'])) {
            $data['processing_min'] = $listing['main']['processing_min'];
        }

        if (isset($listing['main']['processing_max'])) {
            $data['processing_max'] = $listing['main']['processing_max'];
        }

        if (isset($listing['main']['style'])){
            //todo
        }

        if (isset($listing['main']['shop_section_id'])) {
            $data['shop_section_id'] = $listing['main']['shop_section_id'];
        }

        if (!$hasActiveVariations) {
            throw new \Exception('Item with id ' . $listing['main']['itemId'] . 'has no active variations with positive stock.');
        }

        $response = $this->listingService->createListing($this->settingsHelper->getShopSettings('mainLanguage', 'de'), $data);

        if (!isset($response['results']) || !is_array($response['results'])) {
            if (is_array($response) && isset($response['error_msg'])) {
                $message = $response['error_msg'];
            } else if (is_string($response)) {
                $message = $response;
            } else {
                $message = 'Failed to create listing.';
            }

            throw new \Exception($message);
        }

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

            $this->stockRepository->setFilters(['variationId' => $variation['variationId']]);
            $stock = $this->stockRepository->listStockByWarehouseType('sales')->getResult()->first();

            if ($stock->stockNet === NULL || !$variation['isActive'])
            {
                continue;
            }

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
        throw new \Exception();

        $imageList = [];

        $list = array_reverse(array_slice($list, 0, 10));

        foreach ($list as $image) {
            $response = $this->listingImageService->uploadListingImage($listingId, $image['url']);

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
        foreach ($this->settingsHelper->getShopSettings('exportLanguages', [$this->settingsHelper->getShopSettings('mainLanguage', 'de')]) as $language) {
            if ($language != $this->settingsHelper->getShopSettings('mainLanguage', 'de') && $record->itemDescription[$language]['name1'] && strip_tags($record->itemDescription[$language]['description'])) {
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
