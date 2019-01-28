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
        ListingInventoryService $inventoryService)
    {
        $this->itemHelper = $itemHelper;
        $this->listingTranslationService = $listingTranslationService;
        $this->listingService = $listingService;
        $this->deleteListingService = $deleteListingService;
        $this->listingImageService = $listingImageService;
        $this->settingsHelper = $settingsHelper;
        $this->imageHelper = $imageHelper;
        $this->inventoryService = $inventoryService;
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
                $this->fillInventory($listingId, $listing);
            } catch (\Exception $e) {
                $this->listingService->deleteListing($listingId);
            }
            /*
            $listingId = $this->createListing($record);

            if(!is_null($listingId))
            {
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
     * @param Record $record
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
            $data['quantity'] += $variation['data']['stock']['net'];

            //sales price and currency code
            foreach ($variation['data']['salesPrices'] as $salesPrice) {
                $orderReferrer = $this->settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER);

                //todo Währung über Einstellungen vom Kunden definieren lassen
                if (in_array($orderReferrer, $salesPrice['settings']['referrers'])) {
                    if (!isset($data['price']) || (float)$salesPrice['price'] < (float)$data['price']) {
                        $data['price'] = $salesPrice['price'];
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

    private function fillInventory($listingId, $listing)
    {
        /*
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

            foreach ($variation['data']['salesPrices'] as $salesPrice) {
                $orderReferrer = $this->settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER);

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

        $test = json_encode($products);

        $data = [
            'products' => json_encode($products),
            //'price_on_property' => [$dependencies],
            //'quantity_on_property' => [$dependencies]
        ];

        $test = $data;

        $test = $this->inventoryService->updateInventory($listingId, $data, $language);

        $test = 0;

        throw new \Exception();


    }

    /**
     * Add pictures to listing.
     *
     * @param Record $record
     * @param int $listingId
     */
    private function addPictures(Record $record, $listingId)
    {
        $list = $this->itemHelper->getImageList($record->variationImageList['only_current_variation_images_and_generic_images']->toArray(), 'normal');

        $imageList = [];

        $list = array_reverse(array_slice($list, 0, 5));

        foreach ($list as $id => $image) {
            $response = $this->listingImageService->uploadListingImage($listingId, $image);

            if (isset($response['results']) && isset($response['results'][0]) && isset($response['results'][0]['listing_image_id'])) {
                $imageList[] = [
                    'imageId' => $id,
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
    private function publish($listingId, $variationId)
    {
        $data = [
            'state' => 'active',
        ];

        $this->listingService->updateListing($listingId, $data);

        $this->itemHelper->generateSku($listingId, $variationId);
    }
}
