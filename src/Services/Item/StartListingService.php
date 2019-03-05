<?php

namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\Validators\EtsyListingValidator;
use Etsy\Helper\ImageHelper;
use Etsy\Helper\SettingsHelper;
use Etsy\Api\Services\ListingService;
use Etsy\Api\Services\ListingImageService;
use Etsy\Helper\ItemHelper;
use Etsy\Api\Services\ListingTranslationService;
use Plenty\Modules\Frontend\Contracts\CurrencyExchangeRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationExportServiceContract;
use Plenty\Modules\Item\Variation\Services\ExportPreloadValue\ExportPreloadValue;
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
    protected $itemHelper;

    /**
     * @var ListingService
     */
    protected $listingService;

    /**
     * @var DeleteListingService
     */
    protected $deleteListingService;

    /**
     * @var ListingImageService
     */
    protected $listingImageService;

    /**
     * @var ListingTranslationService
     */
    protected $listingTranslationService;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var ListingInventoryService $inventoryService
     */
    protected $inventoryService;

    /**
     * @var $variationExportService
     */
    protected $variationExportService;

    /**
     * @var CurrencyExchangeRepositoryContract
     */
    protected $currencyExchangeRepository;

    /**
     * String values which can be used in properties to represent true
     */
    const BOOL_CONVERTIBLE_STRINGS = ['1', 'y', 'true'];

    /**
     * number of decimals an amount of money gets rounded to
     */
    const moneyDecimals = 2;

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
     * @param CurrencyExchangeRepositoryContract $currencyExchangeRepository
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
        VariationExportServiceContract $variationExportService,
        CurrencyExchangeRepositoryContract $currencyExchangeRepository
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
        $this->currencyExchangeRepository = $currencyExchangeRepository;
    }

    /**
     * Start the listing
     *
     * @param array $listing
     * @throws \Exception
     */
    public function start(array $listing)
    {
        //todo: Bilder updaten/löschen (bei uns)
        if (!isset($listing['main'])) {
            $this->getLogger(__FUNCTION__)->addReference('itemId', $listing['main']['itemId'])
                //todo übersetzen
                ->error('Article is not listable', 'Missing main variation');

            throw new \Exception('Article is not listable. Missing main variation');
        }

        $listing = $this->createListing($listing);
        $listingId = $listing['main']['listingId'];

        try {
            $this->addTranslations($listing, $listingId);
            $this->fillInventory($listingId, $listing);
            $this->addPictures($listingId, $listing);

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

    /**
     * Create a listing base.
     *
     * @param array $listing
     *
     * @throws \Exception
     * @return array
     */
    protected function createListing(array $listing)
    {
        //todo Alle Exceptions und Loggernachrichten mit translator befüllen
        $data = [];
        $failedVariations = [];
        $variationExportService = $this->variationExportService;
        EtsyListingValidator::validateOrFail($listing['main']);


        $data['state'] = 'draft';

        $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');
        //loading etsy currency
        $shops = json_decode($this->settingsHelper->get($this->settingsHelper::SETTINGS_ETSY_SHOPS), true);
        $etsyCurrency = reset($shops)['currency_code'];

        //loading default currency
        $defaultCurrency = $this->currencyExchangeRepository->getDefaultCurrency();

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

            $listing[$key]['failed'] = false;

            $variationExportService->preload($exportPreloadValueList);
            $stock = $variationExportService->getAll($variation['variationId']);
            $stock = $stock[$variationExportService::STOCK];

            if (!isset($variation['sales_price'])) {
                $listing[$key]['failed'] = true;
                //todo übersetzten
                $failedVariations[$variation['variationId']][] = 'Variation has no sales price for Etsy';
            }

            if (!isset($stock) || $stock[0]['stockNet'] < 1) {
                $listing[$key]['failed'] = true;
                //todo übersetzten
                $failedVariations[$variation['variationId']][] = 'Variation has no positive stock';
            }

            if ($listing[$key]['failed']) continue;

            $data['quantity'] += $stock[0]['stockNet'];

            if (!isset($data['price']) || $data['price'] > $variation['sales_price']) {
                if ($defaultCurrency == $etsyCurrency) {
                    $data['price'] = (float)$variation['sales_price'];
                } else {
                    $data['price'] = $this->currencyExchangeRepository->convertFromDefaultCurrency($etsyCurrency,
                        (float) $variation['sales_price'],
                        $this->currencyExchangeRepository->getExchangeRatioByCurrency($etsyCurrency));
                    $data['price'] = round($data['price'], self::moneyDecimals);
                }
            }

            $hasActiveVariations = true;
        }

        //was ist mit mehreren Versandprofilen?? todo
        //shipping profiles
        $data['shipping_template_id'] = $listing['main']['shipping_profiles'][0];

        $data['who_made'] = $listing['main']['who_made'];
        $data['is_supply'] = (in_array(strtolower($listing['main']['is_supply']),
            self::BOOL_CONVERTIBLE_STRINGS));
        $data['when_made'] = $listing['main']['when_made'];

        //Category todo: umbauen auf Standardkategorie
        //$data['taxonomy_id'] = $listing['main']['categories'][0];
        $data['taxonomy_id'] = 1069;

        //Etsy properties
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

        if (isset($listing['main']['style']) && is_string($listing['main']['style'])) {
            $styles = explode(',', $listing['main']['style']);
            $counter = 0;

            foreach ($styles as $style) {
                if (preg_match('@[^\p{L}\p{Nd}\p{Zs}l]u', $style) || $counter > 1) {
                    $this->getLogger(__FUNCTION__)->addReference('itemId', $listing['main']['itemId'])
                        //todo übersetzen
                        ->report('Mapped value for styles contains errors', [$listing['main']['style'], $style]);
                    continue;
                }

                $data['style'][] = $style;
                $counter++;
            }
        }

        if (isset($listing['main']['shop_section_id'])) {
            $data['shop_section_id'] = $listing['main']['shop_section_id'];
        }

        $articleFailed = false;
        $articleErrors = [];

        //logging article errors
        if (!$hasActiveVariations) {
            $articleFailed = true;
            //todo übersetzen
            $articleErrors[] = 'No listable variations';
        }

        if ((!isset($data['title']) || $data['title'] == '')
            || (!isset($data['description']) || $data['description'] == '')) {
            $articleFailed = true;
            //todo übersetzen
            $articleErrors[] = 'Title and description required';
        }

        if (strlen($data['title']) > 140) {
            $articleFailed = true;
            //todo übersetzen
            $articleErrors[] = 'Title can not be longer than 140 characters';
        }

        if (count($listing['main']['attributes']) > 2) {
            $articleFailed = true;
            //todo übersetzen
            $articleErrors[] = 'Article is not allowed to have more than 2 attributes';
        }

        //Logging failed variations
        foreach ($failedVariations as $id => $errors) {
            $this->getLogger(__FUNCTION__)->addReference('variationId', $id)
                //todo übersetzten
                ->error('Variation is not listable', $errors);
        }

        if ($articleFailed) {
            $this->getLogger(__FUNCTION__)->addReference('itemId', $listing['main']['itemId'])
                //todo übersetzen
                ->error('Article is not listable', $articleErrors);
        }

        $response = $this->listingService->createListing($language, $data);

        if (!isset($response['results']) || !is_array($response['results'])) {
            if (is_array($response) && isset($response['error_msg'])) {
                $message = $response['error_msg'];
            } else {
                if (is_string($response)) {
                    $message = $response;
                } else {
                    //todo übersetzten
                    $message = 'Failed to create listing.';
                }
            }

            throw new \Exception($message);
        }

        $results = (array)$response['results'];
        $listing['main']['listingId'] = (int)reset($results)['listing_id'];

        return $listing;
    }

    /**
     * Creates variations for the listing
     *
     * @param $listingId
     * @param $listing
     * @throws \Exception
     */
    protected function fillInventory($listingId, $listing)
    {
        $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');
        $variationExportService = $this->variationExportService;
        $products = [];
        $dependencies = [];

        if (isset($listing['main']['attributes'][0])) {
            $attributeOneId = $listing['main']['attributes'][0]['attributeId'];
            $dependencies[] = $this->inventoryService::CUSTOM_ATTRIBUTE_1;
        }

        if (isset($listing['main']['attributes'][1])) {
            $attributeTwoId = $listing['main']['attributes'][1]['attributeId'];
            $dependencies[] = $this->inventoryService::CUSTOM_ATTRIBUTE_2;
        }

        $exportPreloadValueList = [];
        foreach ($listing as $variation) {
            $exportPreloadValue = pluginApp(ExportPreloadValue::class, [
                'itemId' => $variation['itemId'],
                'variationId' => $variation['variationId']
            ]);

            $exportPreloadValueList[] = $exportPreloadValue;
        }

        $failedVariations = [];
        $hasActiveVariations = false;
        $counter = 0;

        foreach ($listing as $key => $variation) {
            if (!$variation['isActive']) {
                $counter++;
                continue;
            }

            if (isset($variation['failed']) && $variation['failed']) {
                $counter++;
                continue;
            }

            $variationExportService->preload($exportPreloadValueList);
            $stock = $variationExportService->getAll($variation['variationId']);
            $stock = $stock[$variationExportService::STOCK];

            //initialising property values array for articles with no attributes (single variation)
            $products[$counter]['property_values'] = [];

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
                    //todo übersetzen
                    $failedVariations[$key][] = 'Undefined attribute name for language ' . $language . '.';
                    continue 2;
                }

                if (!isset($attributeValueName)) {
                    //todo übersetzen
                    $failedVariations[$key][] = 'Undefined attribute value name for language ' . $language . '.';
                    continue 2;
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

            $price = $variation['sales_price'];

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
                    'quantity' => $stock[0]['stockNet'],
                    'is_enabled' => $variation['isActive']
                ]
            ];

            $products[$counter]['offerings'][0]['price'] = $price;

            $hasActiveVariations = true;
            $counter++;
        }

        //Logging failed variations
        foreach ($failedVariations as $id => $errors) {
            $this->getLogger(__FUNCTION__)->addReference('variationId', $id)
                //todo übersetzten
                ->error('Variation is not listable', $errors);
        }

        //logging failed article
        if (!$hasActiveVariations) {
            $this->getLogger(__FUNCTION__)->addReference('itemId', $listing['main']['itemId'])
                //todo übersetzen
                ->error('Article is not listable', 'Article has no listable variations');
            //todo übersetzen
            throw new \Exception("Can't list article " . $listing['main']['itemId'] . ". No listable variations");
        }

        $data = [
            'products' => json_encode($products),
            'price_on_property' => $dependencies,
            'quantity_on_property' => $dependencies,
            'sku_on_property' => $dependencies
        ];

        $response = $this->inventoryService->updateInventory($listingId, $data, $language);

        if (!isset($response['results']) || !is_array($response['results'])) {
            if (is_array($response) && isset($response['error_msg'])) {
                $message = $response['error_msg'];
            } else {
                if (is_string($response)) {
                    $message = $response;
                } else {
                    //todo übersetzten
                    $message = 'Failed to create listing.';
                }
            }

            throw new \Exception($message);
        }
    }

    /**
     * Add pictures to listing.
     *
     * @param int $listingId
     * @param $listing
     * @throws \Exception
     */
    protected function addPictures($listingId, $listing)
    {
        $list = $listing['main']['images']['all'];

        $list = $this->imageHelper->sortImagePosition($list);

        $imageList = [];

        $list = array_slice($list, 0, 10);

        foreach ($list as $image) {

            if ($image['availabilities']['market'][0] !== -1 && $image['availabilities']['market'][0] !== $this->settingsHelper->get($this->settingsHelper::SETTINGS_ORDER_REFERRER)) {
                continue;
            }

            $response = $this->listingImageService->uploadListingImage($listingId, $image['url'], $image['position']);

            if (!isset($response['results']) || !is_array($response['results'])
                || isset($response['results'][0]) || isset($response['results'][0]['listing_image_id'])) {

                if (is_array($response) && isset($response['error_msg'])) {
                    $message = $response['error_msg'];
                } else {
                    if (is_string($response)) {
                        $message = $response;
                    } else {
                        //todo übersetzten
                        $message = 'Failed to create listing.';
                    }
                }

                $this->getLogger(__FUNCTION__)->addReference('imageId', $image['id'])
                    //todo übersetzen
                    ->error('Image not listable', $message);
            }

            $imageList[] = [
                'imageId' => $image['id'],
                'listingImageId' => $response['results'][0]['listing_image_id'],
                'listingId' => $response['results'][0]['listing_id'],
                'imageUrl' => $image['url']
            ];
        }

        if (!count($imageList)) {
            $this->getLogger(__FUNCTION__)->addReference('itemId', $listing['main']['itemId'])
                //todo übersetzen
                ->error('Article is not listable', 'Article has no listable images');
            //todo übersetzen
            throw new \Exception("Can't list article " . $listing['main']['itemId'] . ". No listable images");
        }

        $this->imageHelper->save($listing['main']['variationId'], json_encode($imageList));
    }


    /**
     * @param array $listing
     * @param $listingId
     * @throws \Exception
     */
    protected function addTranslations(array $listing, $listingId)
    {
        foreach ($this->settingsHelper->getShopSettings('exportLanguages',
            [$this->settingsHelper->getShopSettings('mainLanguage', 'de')]) as $language) {

            foreach ($listing['main']['texts'] as $text) {
                if ($text['lang'] == $this->settingsHelper->getShopSettings('mainLanguage', 'de')
                    || $text['lang'] != $language
                    || !$text['name1']
                    || !strip_tags($text['description'])
                ) {
                    continue;
                }
                try {
                    $title = trim(preg_replace('/\s+/', ' ', $text['name1']));
                    $title = ltrim($title, ' +-!?');
                    $legalInformation = $this->itemHelper->getLegalInformation($language);
                    $description = html_entity_decode(strip_tags($text['description'] . $legalInformation));

                    $data = [
                        'title' => $title,
                        'description' => $description
                    ];

                    /*todo: tags need to be transalted as soon as they are implemented
                    if ($record->itemDescription[$language]['keywords']) {
                        $data['tags'] = $this->itemHelper->getTags($record, $language);
                    }
                    */

                    $this->listingTranslationService->createListingTranslation($listingId, $language, $data);
                } catch (\Exception $ex) {
                    $this->getLogger(__FUNCTION__)
                        ->addReference('etsyListingId', $listingId)
                        ->addReference('variationId', $listing['main']['variationId'])
                        ->addReference('etsyLanguage', $language)
                        ->error('Etsy::item.translationUpdateError', $ex->getMessage());
                }
            }
        }
    }

    /**
     * @param int $listingId
     * @param $listing
     */
    protected function publish($listingId, $listing)
    {
        $data = [
            'state' => 'active',
        ];

        $this->listingService->updateListing($listingId, $data);
        $this->itemHelper->updateListingSkuStatuses($listing, $this->itemHelper::SKU_STATUS_ACTIVE);
    }
}
