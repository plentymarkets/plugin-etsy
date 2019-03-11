<?php

namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingImageService;
use Etsy\Api\Services\ListingInventoryService;
use Etsy\Api\Services\ListingTranslationService;
use Etsy\Helper\ImageHelper;
use Etsy\Validators\EtsyListingValidator;
use Plenty\Modules\Frontend\Contracts\CurrencyExchangeRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationExportServiceContract;
use Plenty\Modules\Item\Variation\Services\ExportPreloadValue\ExportPreloadValue;
use Plenty\Plugin\ConfigRepository;
use Etsy\Api\Services\ListingService;
use Etsy\Helper\ItemHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;

/**
 * Class UpdateListingService
 */
class UpdateListingService
{
    use Loggable;

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @var SettingsHelper
     */
    private $settingsHelper;

    /**
     * @var ItemHelper
     */
    private $itemHelper;

    /**
     * @var ListingService
     */
    private $listingService;

    /**
     * @var ListingTranslationService
     */
    private $listingTranslationService;
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var ListingInventoryService
     */
    protected $listingInventoryService;

    /**
     * @var ListingImageService
     */
    protected $listingImageService;

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
     * UpdateListingService constructor.
     * @param ItemHelper $itemHelper
     * @param ConfigRepository $config
     * @param ListingService $listingService
     * @param SettingsHelper $settingsHelper
     * @param ImageHelper $imageHelper
     * @param ListingTranslationService $listingTranslationService
     * @param ListingInventoryService $listingInventoryService
     * @param Translator $translator
     * @param ListingImageService $listingImageService
     * @param CurrencyExchangeRepositoryContract $currencyExchangeRepository
     */
    public function __construct(
        ItemHelper $itemHelper,
        ConfigRepository $config,
        ListingService $listingService,
        SettingsHelper $settingsHelper,
        ImageHelper $imageHelper,
        ListingTranslationService $listingTranslationService,
        ListingInventoryService $listingInventoryService,
        Translator $translator,
        ListingImageService $listingImageService,
        VariationExportServiceContract $variationExportService,
        CurrencyExchangeRepositoryContract $currencyExchangeRepository
    ) {
        $this->config = $config;
        $this->settingsHelper = $settingsHelper;
        $this->itemHelper = $itemHelper;
        $this->listingService = $listingService;
        $this->listingTranslationService = $listingTranslationService;
        $this->listingInventoryService = $listingInventoryService;
        $this->translator = $translator;
        $this->imageHelper = $imageHelper;
        $this->listingImageService = $listingImageService;
        $this->currencyExchangeRepository = $currencyExchangeRepository;
        $this->variationExportService = $variationExportService;
    }

    /**
     * Update the listing
     *
     * @param array $listing
     */
    public function update(array $listing)
    {
        $listingId = $listing['main']['skus'][0]['parentSku'];

        try {
            $this->updateListing($listing, $listingId);
            $this->updateInventory($listing, $listingId);
	        $this->updateImages($listing, $listingId);
	        $this->addTranslations($listing, $listingId);
        } catch (\Exception $e) {

        }
    }

    /**
     * @param array $listing
     * @param int $listingId
     * @return int
     * @throws \Exception
     */
    private function updateListing(array $listing, int $listingId)
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
               // if (isset(reset($variation['skus'])))
                //$this->itemHelper->de
                //todo sku der variante löschen
                continue;
            }

            $listing[$key]['failed'] = false;

            if (!isset($variation['sales_price'])) {
                $listing[$key]['failed'] = true;
                //todo übersetzten
                $failedVariations[$variation['variationId']][] = 'Variation has no sales price for Etsy';
            }

            if ($listing[$key]['failed']) continue;

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

        //shipping profiles
        $data['shipping_template_id'] = reset($listing['main']['shipping_profiles']);

        $data['who_made'] = $listing['main']['who_made'];
        $data['is_supply'] = in_array(strtolower($listing['main']['is_supply']),
            self::BOOL_CONVERTIBLE_STRINGS);
        $data['when_made'] = $listing['main']['when_made'];

        //Category
        $data['taxonomy_id'] = reset($listing['main']['categories']);

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
            $data['is_customizable'] = in_array(strtolower($listing['main']['is_customizable']),
                self::BOOL_CONVERTIBLE_STRINGS);
        }

        if (isset($listing['main']['non_taxable'])) {
            $data['non_taxable'] = in_array(strtolower($listing['main']['non_taxable']),
                self::BOOL_CONVERTIBLE_STRINGS);
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

            $this->listingService->updateListing($listingId, ['state' => 'inactive']);

            throw new \Exception('Listing of article' . $listing['main']['itemId'] . ' got set to inactive');
        }
        $response = $this->listingService->updateListing($listingId, $data, $language);

        if (!isset($response['results']) || !is_array($response['results'])) {
            if (is_array($response) && isset($response['error_msg'])) {
                $message = $response['error_msg'];
            } else {
                if (is_string($response)) {
                    $message = $response;
                } else {
                    $message = 'Failed to update listing.';
                }
            }

            throw new \Exception($message);
        }
        $results = (array)$response['results'];

        return (int)reset($results)['listing_id'];
    }

    /**
     * @param array $listing
     * @param int $listingId
     * @throws \Exception
     */
    public function updateInventory(array $listing, int $listingId)
    {
        $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');
        $products = [];
        $dependencies = [];

        if (isset($listing['main']['attributes'][0])) {
            $attributeOneId = $listing['main']['attributes'][0]['attributeId'];
            $dependencies[] = $this->listingInventoryService::CUSTOM_ATTRIBUTE_1;
        }

        if (isset($listing['main']['attributes'][1])) {
            $attributeTwoId = $listing['main']['attributes'][1]['attributeId'];
            $dependencies[] = $this->listingInventoryService::CUSTOM_ATTRIBUTE_2;
        }

        $counter = 0;

        foreach ($listing as $variation) {

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
                    throw new \Exception("Can't list variation " . $variation['variationId'] . ". Undefined attribute name for language " . $language . ".");
                }

                if (!isset($attributeValueName)) {
                    throw new \Exception("Can't list variation " . $variation['variationId'] . ". Undefined attribute value name for language " . $language . ".");
                }

                if (isset($attributeOneId) && $attribute['attributeId'] == $attributeOneId) {
                    $products[$counter]['property_values'][] = [
                        'property_id' => $this->listingInventoryService::CUSTOM_ATTRIBUTE_1,
                        'property_name' => $attributeName,
                        'values' => [$attributeValueName],
                    ];
                } elseif (isset($attributeTwoId) && $attribute['attributeId'] == $attributeTwoId) {
                    $products[$counter]['property_values'][] = [
                        'property_id' => $this->listingInventoryService::CUSTOM_ATTRIBUTE_2,
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

        $this->listingInventoryService->updateInventory($listingId, $data, $language);
    }

    public function updateImages($listing, $listingId)
    {
        $etsyImages = json_decode($this->settingsHelper->get($listing['main']['itemId']));

        $list = $listing['main']['images']['all'];

        foreach ($list as $key => $image) {
            if (!isset($image['availabilities']['market'][0]) || ($image['availabilities']['market'][0] !== -1
                    && $image['availabilities']['market'][0] !== $this->settingsHelper->get($this->settingsHelper::SETTINGS_ORDER_REFERRER))) {
                unset($list[$key]);
            }
        }

        $list = array_slice($list, 0, 10);

        foreach ($etsyImages as $etsyKey => $etsyImage){
            foreach ($list as $plentyKey => $plentyImage){
                if ($etsyImage['imageId'] == $plentyImage['imageId'])
                {
                    unset($etsyImages[$etsyKey]);
                }
            }
        }

        $imageList = [];

        $list = $this->imageHelper->sortImagePosition($list);

        foreach ($list as $image) {

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


        foreach ($etsyImages as $etsyImage){
            //todo response handling
            $response = $this->listingImageService->deleteListingImage($listingId, $etsyImage['imageId']);
        }

        $this->imageHelper->update($listing['main']['itemId'], json_encode($imageList));

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
}
