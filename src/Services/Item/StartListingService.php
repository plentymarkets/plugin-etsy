<?php

namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingInventoryService;
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
                //$this->addPictures($listingId, $listing); todo
                //todo: translations
                $this->fillInventory($listingId, $listing);
            } catch (\Exception $e) {
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

        foreach ($listing as $variation) {
            $this->stockRepository->setFilters(['variationId' => $variation['variationId']]);
            $stock = $this->stockRepository->listStockByWarehouseType('sales')->getResult()->first();

            if ($stock->stockNet === NULL)
            {
                continue;
            }

            $data['quantity'] += $stock->stockNet;

            //loading default currency
            /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
            $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
            $webStoreConfiguration = $webstoreConfigurationRepository->findByPlentyId(pluginApp(Application::class)->getPlentyId());
            $defaultCurrency = $webStoreConfiguration->defaultCurrency;

            //todo: Nur den in den Eimstellungen definierten Preis für Etsy nutzen und auf Shopwährung prüfen. Ggf. umrechnen
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

        //was ist mit mehreren Versandprofilen?? todo
        $data['shipping_template_id'] = $listing['main']['shipping_profiles'][0];

        //who_made -> gemappte eigenschaft des kunden
        $data['who_made'] = $listing['main']['who_made'];
        //is_supply ->
        $data['is_supply'] = ($listing['main']['is_supply'] == 1) ? true : false;
        //when_made -> ^
        $data['when_made'] = $listing['main']['when_made'];

        //Kategorie todo: umbauen auf Standardkategorie
        $data['taxonomy_id'] = $listing['main']['categories'][0];


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

        //validating required fields
        //todo auslagern in Validator
        /*
        if (!isset($data['title']) || $data['title'] == '') {
            throw new \Exception('Required field title is not allowed to be empty. Item ID ' . $listing['main']['itemId']);
        }

        if (!isset($data['description']) || $data['description'] == '') {
            throw new \Exception('Required field description is not allowed to be empty. Item ID ' . $listing['main']['itemId']);
        }

        if (!isset($data['price']) || $data['price'] == 0) {
            throw new \Exception('Required field price is not allowed to be empty or 0. Item ID ' . $listing['main']['itemId']);
        }

        if (!isset($data['quantity']) || $data['quantity'] == 0) {
            throw new \Exception('No . Item ID ' . $listing['main']['itemId']);
        }

        if (!isset($data['taxonomy_id']) || $data['taxonomy_id'] == null) {
            throw new \Exception('');
        }
        */

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


        /* todo: Listing anlegen (Artikeldaten)
         * required:
         * languague?               Abklären wie man die Sprache definiert, bzw. ob das in createListing möglich ist
         * quantity
         * title
         * description
         * price
         * shipping_template_id
         * who_made
         * is_supply
         * when_made
         *
         * optional:
         * shop_section_id
         * image_ids
         * is_customizable
         * non_taxable
         * image
         * state
         * processing_min
         * processing_max
         * category_id
         * taxonomy_id
         * tags
         * recipient
         * occasion
         * style
         */


        // TODO
        // materials
        // shop_section_id
        // processing_min
        // processing_max
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

            //todo: skus pflegen
            $products[$counter]['sku'] = '';

            $products[$counter]['offerings'] = [
                [
                    //todo Bestand pflegen
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
            'quantity_on_property' => $dependencies
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

        $imageList = [];

        $list = array_reverse(array_slice($list, 0, 10));

        foreach ($list as $image) {
            $image['url'] = 'https://cdn.pixabay.com/photo/2013/08/11/19/46/coffee-171653_1280.jpg'; //todo: Das ist nur zum Testen, später entfernen
            $response = $this->listingImageService->uploadListingImage($listingId, $image['url']);

            if (isset($response['results']) && isset($response['results'][0]) && isset($response['results'][0]['listing_image_id'])) {
                $imageList[] = [
//                    'imageId' => $id,
                    'listingImageId' => $response['results'][0]['listing_image_id'],
                    'listingId' => $response['results'][0]['listing_id'],
                    'imageUrl' => $image,
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
        foreach ($listing as $variation)
        {
            $this->itemHelper->generateParentSku($listingId, $variation);
        }

    }
}
