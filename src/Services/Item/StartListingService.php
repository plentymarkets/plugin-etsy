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

//            $this->addPictures($listingId, $listing);

            try {
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
        /*todo: WICHTIG Katalog als Datenquelle verwenden
         * Es wird IMMER die Hauptvariante mitgeladen
         * Felder die nicht durch Plentyfelder abgedeckt sind und sich auf Artikelebene beziehen werden als Eigenschaften an der Hauptvariante hinterlegt
         * Felder die nicht durch Plentyfelder abgedeckt sind und sich auf Varianten beziehen werden als Eigenschaften an den einzelnen Varianten hinterlegt
         *
         * Benötigte Daten:
         * Hauptvarianten & zugeordnete Eigenschaften
         */

        /*
         * Ablauf:
         * Listing anlegen
         * Inventory befüllen
         * Listing aktivieren
         */

        $data = [];
        $itemShippingProfilesRepository = pluginApp(ItemShippingProfilesRepositoryContract::class);

        $data['state'] = 'draft';

        $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');
        $exportLanguages = $this->settingsHelper->getShopSettings('exportLanguages', $language);

        //$data['language'] = $language;

        //title and description
        foreach ($listing['main']['data']['texts'] as $text) {
            if ($text['lang'] == $language) {
                $data['title'] = str_replace(':', ' -', $text['name1']);
                $data['title'] = ltrim($data['title'], ' +-!?');

                $data['description'] = $text['description'];
            }
        }


        //quantity & price
        $data['quantity'] = 0;

        foreach ($listing as $variation) {
            $this->stockRepository->setFilters(['variationId' => $variation['id']]);
            $stock = $this->stockRepository->listStockByWarehouseType('sales')->getResult()->first();

            if ($stock->stockNet === NULL)
            {
                continue;
            }

            $data['quantity'] += $stock->stockNet;

            //sales price and currency code
            foreach ($variation['data']['salesPrices'] as $salesPrice) {
                $orderReferrer = $this->settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER);

                //todo Währung über Einstellungen vom Kunden definieren lassen
                if (in_array($orderReferrer, $salesPrice['settings']['referrers'])) {
                    if (!isset($data['price']) || $salesPrice['price'] < $data['price']) {
                        $data['price'] =  (float) $salesPrice['price'];
                    }
                    break;
                }
            }
        }

        //todo: data befüllen
        //languages? (Übersetzungen)
        //quantity -> varianten bestand zusammenzählen
        //description
        //price -> Währung über Einstellungen definieren und in die Abfrage implementieren

        //shipping template id
        /** @var Collection $shippingProfiles */
        $shippingProfiles = $itemShippingProfilesRepository->findByItemId($listing[0]['data']['item']['id']);
        $data['shipping_template_id'] = $this->itemHelper->getShippingTemplateId($shippingProfiles);

        //who_made -> gemappte eigenschaft des kunden
        $data['who_made'] = 'i_did';
        //is_supply ->
        $data['is_supply'] = false;
        //when_made -> ^
        $data['when_made'] = 'made_to_order';

        // Kategorie
        if (isset($listing['main']['data']['defaultCategories'][0]['id'])
            && $listing['main']['data']['defaultCategories'][0]['id'] == 75) {
            $data['taxonomy_id'] = 1069;
        } else {
            $data['taxonomy_id'] = 1102;
        }

        if (false) {
            $data['tags'] = '';
        }

        if (false) {
            $data['occasion'] = '';
        }

        if (false) {
            $data['recipient'] = '';
        }

        if (false) {
            $data['item_weight'] = '';
            $data['item_weight_units'] = 'g';
        }

        if (false) {
            $data['item_height'] = '';
            $data['item_dimensions_unit'] = 'mm';
        }

        if (false) {
            $data['item_length'] = '';
            $data['item_dimensions_unit'] = 'mm';
        }

        if (false) {
            $data['item_width'] = '';
            $data['item_dimensions_unit'] = 'mm';
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

        // TODO 'en' und 'de' dynamisch aus dem dynamodb repo ziehen

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
        /* todo: auf Katalog arrays anpassen
         * Varianten bauen
         * Inventory updaten
         */

        //ATTRIBUTE LADEN
        //Prüfen ob Attribute < 2
        //Attributwerte products zuweisen
        //inventory befüllen

        $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');
        $products = [];
        $dependencies = [];

        if (count($listing['main']['data']['attributes']) > 2) {
            throw new \Exception("Can't list article " . $listing['main']['data']['item']['id'] . ". Too many attributes.");
        }

        if (isset($listing['main']['data']['attributes'][0])) {
            $attributeOneId = $listing['main']['data']['attributes'][0]['attributeId'];
            $dependencies[] = $this->inventoryService::CUSTOM_ATTRIBUTE_1;
        }

        if (isset($listing['main']['data']['attributes'][1])) {
            $attributeTwoId = $listing['main']['data']['attributes'][1]['attributeId'];
            $dependencies[] = $this->inventoryService::CUSTOM_ATTRIBUTE_2;
        }

        $counter = 0;

        foreach ($listing as $variation) {

            foreach ($variation['data']['attributes'] as $attribute) {

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
                    throw new \Exception("Can't list variation " . $variation['variation']['id'] . ". Undefined attribute name for language " . $language . ".");
                }

                if (!isset($attributeValueName)) {
                    throw new \Exception("Can't list variation " . $variation['variation']['id'] . ". Undefined attribute value name for language " . $language . ".");
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
            foreach ($variation['data']['salesPrices'] as $salesPrice) {

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
                    'is_enabled' => $variation['data']['variation']['isActive']
                ]
            ];

            if (isset($price)) {
                $products[$counter]['offerings'][0]['price'] = $price;
            }

            $counter++;
        }

        if ($counter == 0) {
            throw new \Exception("Can't list article " . $listing['main']['data']['item']['id'] . ". No active variations");
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
        $list = $listing['main']['data']['images']['all'];

        $imageList = [];

        $list = array_reverse(array_slice($list, 0, 10));

        foreach ($list as $image) {
            $response = $this->listingImageService->uploadListingImage($listingId, $image['url']);

            if (isset($response['results']) && isset($response['results'][0]) && isset($response['results'][0]['listing_image_id'])) {
                $imageList[] = [
//                    'imageId' => $id,
                    'listingImageId' => $response['results'][0]['listing_image_id'],
                    'listingId' => $response['results'][0]['listing_id'],
                    'imageUrl' => $image,
                ];

            }
        }

        if (count($imageList)) {
            $this->imageHelper->save($record->variationBase->id, json_encode($imageList));
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
