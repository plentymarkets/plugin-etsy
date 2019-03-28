<?php

namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingImageService;
use Etsy\Api\Services\ListingInventoryService;
use Etsy\Api\Services\ListingTranslationService;
use Etsy\EtsyServiceProvider;
use Etsy\Exceptions\ListingException;
use Etsy\Helper\ImageHelper;
use Etsy\Validators\EtsyListingValidator;
use Illuminate\Support\MessageBag;
use Plenty\Modules\Frontend\Contracts\CurrencyExchangeRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationExportServiceContract;
use Plenty\Modules\Item\Variation\Services\ExportPreloadValue\ExportPreloadValue;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Plugin\ConfigRepository;
use Etsy\Api\Services\ListingService;
use Etsy\Helper\ItemHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;
use Plenty\Exceptions\ValidationException;

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
     * number of decimals an counter of money gets rounded to
     */
    const MONEY_DECIMALS = 2;

    /**
     * number of decimals an counter of money gets rounded to
     */
    const MINIMUM_PRICE = 0.18;

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
        $listingId = 0;

        foreach ($listing as $variation) {
            if (isset($variation['skus'][0]['parentSku'])) {
                $listingId = $variation['skus'][0]['parentSku'];
                break;
            }
        }

        try {
            $listing = $this->updateListing($listing, $listingId);
            $listing = $this->updateInventory($listing, $listingId);
	        $this->updateImages($listing, $listingId);
	        $this->addTranslations($listing, $listingId);
	        $this->publish($listingId, $listing);
        } catch (ListingException $listingException) {
            $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_INVENTORY)
                ->addReference('itemId', $listing['main']['itemId'])
                ->addReference('etsyListingId', $listingId)
                ->error($listingException->getMessage(), $listingException->getMessageBag());
        } catch (ValidationException $validationException) {
            $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_INVENTORY)
                ->addReference('itemId', $listing['main']['itemId'])
                ->addReference('etsyListingId', $listingId)
                ->error($validationException->getMessage(), $validationException->getMessageBag());
        } catch (\Exception $exception) {
            $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_SERVICE)
                ->addReference('itemId', $listing['main']['itemId'])
                ->addReference('etsyListingId', $listingId)
                ->error($exception->getMessage());
        }
    }

    /**
     * @param array $listing
     * @param int $listingId
     * @return array
     * @throws ListingException
     */
    private function updateListing(array $listing, int $listingId)
    {
        $data = [];
        $failedVariations = [];
        EtsyListingValidator::validateOrFail($listing['main']);

        $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');

        //title and description
        foreach ($listing['main']['texts'] as $text) {
            if ($text['lang'] == $language) {
                $data['title'] = str_replace(':', ' -', $text['name1']);
                $data['title'] = ltrim($data['title'], ' +-!?');

                $data['description'] = html_entity_decode(strip_tags($text['description']));
            }
        }

        $hasActiveVariations = false;

        foreach ($listing as $key => $variation) {
            if (!$variation['isActive']) {
                continue;
            }

            $listing[$key]['failed'] = false;

            if (!isset($variation['sales_price'])) {
                $listing[$key]['failed'] = true;
                $failedVariations[$variation['variationId']][] = $this->translator
                    ->trans(EtsyServiceProvider::PLUGIN_NAME.'log.variationPriceMissing');
            }

            if ($listing[$key]['failed']) continue;

            $hasActiveVariations = true;
        }

        //shipping profiles
        $data['shipping_template_id'] = (int) reset($listing['main']['shipping_profiles']);

        $data['who_made'] = $listing['main']['who_made'];
        $data['is_supply'] = in_array(strtolower($listing['main']['is_supply']),
            self::BOOL_CONVERTIBLE_STRINGS);
        $data['when_made'] = $listing['main']['when_made'];

        //Category
        $data['taxonomy_id'] = (int) reset($listing['main']['categories']);

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
            $data['item_weight_unit'] = 'g';
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
            $materials = explode(',', $listing['main']['materials']);
            $counter = 0;

            foreach ($materials as $key => $material) {
                if ($counter > 13) break;

                if (preg_match('@[^\p{L}\p{Nd}\p{Zs}l]@u', $material) > 0 || $material == "") {
                    $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                        ->addReference('itemId', $listing['main']['itemId'])
                        ->warning(EtsyServiceProvider::PLUGIN_NAME . '::log.wrongMaterialFormat',
                            [$listing['main']['materials'], $material]);
                    continue;
                }

                $data['materials'][] = $material;
                $counter++;
            }

            if ($counter > 0){
                $data['materials'] = implode(',', $data['materials']);
            }
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
                if ($counter > 1) break;

                if (preg_match('@[^\p{L}\p{Nd}\p{Zs}l]@u', $style) > 0 || $style == "") {
                    $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                        ->addReference('itemId', $listing['main']['itemId'])
                        ->warning(EtsyServiceProvider::PLUGIN_NAME.'::log.wrongStyleFormat',
                            [$listing['main']['style'], $style]);
                    continue;
                }

                $data['style'][] = $style;
                $counter++;
            }

            if ($counter > 0) {
                $data['style'] = implode(',', $data['style']);
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
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME.'::log.noVariations');
        }

        if ((!isset($data['title']) || $data['title'] == '')
            || (!isset($data['description']) || $data['description'] == '')) {
            $articleFailed = true;
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME.'::log.wrongTitleOrDescription');
        }

        if (strlen($data['title']) > 140) {
            $articleFailed = true;
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME.'::log.longTitle');
        }

        if (count($listing['main']['attributes']) > 2) {
            $articleFailed = true;
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME.'::log.tooManyAttributes');
        }

        //Logging failed variations
        foreach ($failedVariations as $id => $errors) {
            $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_SERVICE)
                ->addReference('variationId', $id)
                ->error(EtsyServiceProvider::PLUGIN_NAME.'::log.', $errors);
        }

        if ($articleFailed || count($failedVariations)) {
            $exceptionMessage = ($articleFailed) ? '::log.articleNotListable' : '::log.variationsNotListed';

            foreach ($failedVariations as $variationId => $variationErrors) {
                $failedVariations[$variationId] = implode(",\n", $variationErrors);
            }

            if ($articleFailed) {
                $errors = array_merge($articleErrors, $failedVariations);
                $messageBag = pluginApp(MessageBag::class, ['messages' => $errors]);
                throw new ListingException($messageBag, EtsyServiceProvider::PLUGIN_NAME.$exceptionMessage);
            }

            $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_SERVICE)
                ->addReference('itemId', $listing['main']['itemId'])
                ->error($exceptionMessage, $failedVariations);
        }

        $response = $this->listingService->updateListing($listingId, $data, $language);

        if (!isset($response['results']) || !is_array($response['results'])) {
            $messages = [];

            if (is_array($response) && isset($response['error_msg'])) {
                $messages[] = $response['error_msg'];
            } else {
                if (is_string($response)) {
                    $messages[] = $response;
                } else {
                    $messages[] = $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.emptyResponse');
                }
            }

            $messageBag = pluginApp(MessageBag::class, ['messages' => $messages]);
            throw new ListingException($messageBag,
                $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.updateListingError'));
        }

        return $listing;
    }

    /**
     * @param array $listing
     * @param int $listingId
     * @return array
     * @throws \Exception
     */
    public function updateInventory(array $listing, int $listingId)
    {
        $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');
        $variationExportService = $this->variationExportService;
        $products = [];
        $dependencies = [];

        //Will contain the ids of the variations that got5 an sku, so we can delete the skus if an error occurs
        $newVariations = [];

        //loading etsy currency
        $shops = json_decode($this->settingsHelper->get($this->settingsHelper::SETTINGS_ETSY_SHOPS), true);
        $etsyCurrency = reset($shops)['currency_code'];

        //loading default currency
        $defaultCurrency = $this->currencyExchangeRepository->getDefaultCurrency();

        if (isset($listing['main']['attributes'][0])) {
            $attributeOneId = $listing['main']['attributes'][0]['attributeId'];
            $dependencies[] = $this->listingInventoryService::CUSTOM_ATTRIBUTE_1;
        }

        if (isset($listing['main']['attributes'][1])) {
            $attributeTwoId = $listing['main']['attributes'][1]['attributeId'];
            $dependencies[] = $this->listingInventoryService::CUSTOM_ATTRIBUTE_2;
        }

        $variationExportService->addPreloadTypes([$variationExportService::STOCK]);
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
            if (!$variation['isActive'] && !isset($variation['skus'][0]['sku'])) {
                continue;
            }

            if (isset($variation['failed']) && $variation['failed']) {
                continue;
            }

            $variationExportService->preload($exportPreloadValueList);
            $stock = $variationExportService->getAll($variation['variationId']);
            $stock = $stock[$variationExportService::STOCK];

            //initialising property values array for articles with no attributes (single variation)
            $products[$counter]['property_values'] = [];

            $attributes = $variation['attributes'];

            /**
             * @var array $attributes
             */
            foreach ($attributes as $attribute) {
                /**
                 * @var array $attribute['attribute']
                 */
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
                    $variation['failed'] = true;
                    $failedVariations['variation-' . $variation['variationId']][] = $this->translator
                        ->trans(EtsyServiceProvider::PLUGIN_NAME.'::log.attributeNameMissing');
                    continue 2;
                }

                if (!isset($attributeValueName)) {
                    $variation['failed'] = true;
                    $failedVariations['variation-' . $variation['variationId']][] = $this->translator
                        ->trans(EtsyServiceProvider::PLUGIN_NAME.'::log.attributeValueNameMissing');
                    continue 2;
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

            if ($defaultCurrency == $etsyCurrency) {
                $price = (float)$variation['sales_price'];
            } else {
                $price = $this->currencyExchangeRepository->convertFromDefaultCurrency($etsyCurrency,
                    (float) $variation['sales_price'],
                    $this->currencyExchangeRepository->getExchangeRatioByCurrency($etsyCurrency));
                $price = round($price, self::MONEY_DECIMALS);
            }

            if (!$this->itemHelper->updateVariationSkuTimestamp($variation['variationId']))
            {
                //Creating a formatted array so the method can use the data
                $products[$counter]['sku'] = $this->itemHelper->generateParentSku($listingId, [
                    'id' => $variation['variationId'],
                    'data' => [
                        'item' => [
                            'id' => $variation['itemId']
                        ]
                    ]
                ]);

                $newVariations[] = $variation['variationId'];
            } else {
                $products[$counter]['sku'] = $variation['skus'][0]['sku'];
            }

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

        //logging failed article / variations
        if (!$hasActiveVariations || count($failedVariations)) {
            $exceptionMessage = (!$hasActiveVariations) ? 'log.articleNotListable' : 'log.variationsNotListed';

            foreach ($failedVariations as $variationId => $variationErrors) {
                $failedVariations[$variationId] = implode(",\n", $variationErrors);
            }

            if (!$hasActiveVariations) {
                $errors = array_unshift($failedVariations, $this->translator
                    ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.noVariations'));
                $messageBag = pluginApp(MessageBag::class, ['messages' => $errors]);
                throw new ListingException($messageBag, $exceptionMessage);
            }
        }

        $data = [
            'products' => json_encode($products),
            'price_on_property' => $dependencies,
            'quantity_on_property' => $dependencies,
            'sku_on_property' => $dependencies
        ];

        $response = $this->listingInventoryService->updateInventory($listingId, $data, $language);

        if (!isset($response['results']) || !is_array($response['results'])) {
            $messages = [];

            if (is_array($response) && isset($response['error_msg'])) {
                $messages[] = $response['error_msg'];
            } else {
                if (is_string($response)) {
                    $messages[] = $response;
                } else {
                    $messages[] = $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.emptyResponse');
                }
            }

            $messageBag = pluginApp(MessageBag::class, ['messages' => $messages]);

            foreach ($newVariations as $variationId) {
                /** @var VariationSku $sku */
                $sku = $this->itemHelper->getVariationSku($variationId);

                if ($sku) {
                    try {
                        $this->itemHelper->deleteSku($sku->id);
                    } catch(\Exception $ex)
                    {
                        $this->getLogger(__FUNCTION__)->debug('Etsy::item.skuRemovalError', [
                            'skuId' => $sku->id,
                            'variationId' => $variationId,
                            'listingId' => $listingId,
                            'error' => $ex->getMessage()
                        ]);
                    }
                }
            }

            throw new ListingException($messageBag,
                $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.updateInventoryError'));
        }

        return $listing;
    }

    /**
     * @param $listing
     * @param $listingId
     * @throws ListingException
     */
    public function updateImages($listing, $listingId)
    {
        $etsyImages = json_decode($this->imageHelper->get((string) $listingId), true);

        $list = $listing['main']['images']['all'];

        foreach ($list as $key => $image) {
            if (!isset($image['availabilities']['market'][0]) || ($image['availabilities']['market'][0] !== -1
                    && $image['availabilities']['market'][0] != $this->settingsHelper
                        ->get($this->settingsHelper::SETTINGS_ORDER_REFERRER))) {
                unset($list[$key]);
            }
        }

        $list = array_slice($list, 0, 10);

        foreach ($etsyImages as $etsyKey => $etsyImage){
            foreach ($list as $plentyKey => $plentyImage){
                if ($etsyImage['imageId'] == $plentyImage['id'])
                {
                    unset($etsyImages[$etsyKey]);
                }
            }
        }

        foreach ($etsyImages as $etsyImage){
            $response = $this->listingImageService->deleteListingImage($listingId, $etsyImage['listingImageId']);

            if (!isset($response['results']) || !is_array($response['results'])) {
                $messages = [];

                if (is_array($response) && isset($response['error_msg'])) {
                    $messages[] = $response['error_msg'];
                } else {
                    if (is_string($response)) {
                        $messages[] = $response;
                    } else {
                        $messages[] = $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.emptyResponse');
                    }
                }

                $messageBag = pluginApp(MessageBag::class, ['messages' => $messages]);
                throw new ListingException($messageBag,
                    $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.deleteListingImageError'));
            }
        }

        $imageList = [];

        $list = $this->imageHelper->sortImagePosition($list);

        foreach ($list as $image) {

            $response = $this->listingImageService->uploadListingImage($listingId, $image['url'], $image['position']);

            if (!isset($response['results']) || !is_array($response['results'])
                || !isset($response['results'][0]) || !isset($response['results'][0]['listing_image_id'])) {
                $messages = [];

                if (is_array($response) && isset($response['error_msg'])) {
                    $messages[] = $response['error_msg'];
                } else {
                    if (is_string($response)) {
                        $messages[] = $response;
                    } else {
                        $messages[] = $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.emptyResponse');
                    }
                }

                $messageBag = pluginApp(MessageBag::class, ['messages' => $messages]);
                throw new ListingException($messageBag,
                    $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.uploadListingImageError'));
            }

            $imageList[] = [
                'imageId' => $image['id'],
                'listingImageId' => $response['results'][0]['listing_image_id'],
                'listingId' => $response['results'][0]['listing_id'],
                'itemId' => $image['itemId'],
                'imageUrl' => $image['url']
            ];
        }

        $this->imageHelper->update($listingId, json_encode($imageList));

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
                    $this->getLogger(EtsyServiceProvider::ADD_LISTING_TRANSLATIONS)
                        ->addReference('etsyListingId', $listingId)
                        ->addReference('variationId', $listing['main']['variationId'])
                        ->addReference('etsyLanguage', $language)
                        ->error('Etsy::item.translationUpdateError', $ex->getMessage());
                }
            }
        }
    }

    public function publish($listingId, $listing)
    {
        $data = [
            'state' => 'active',
        ];

        if (isset($listing['main']['renew'])) {
            //this parameter decides if the listing gets automatically renewed on etsy when it's stock gets positive after
            //being 0. This creates costs for the customer, so he has the possibility to set it to false
            //IMPORTANT, will always set the listing state to active if it's possible
            $data['should_auto_renew'] = in_array(strtolower($listing['main']['renew']),
                self::BOOL_CONVERTIBLE_STRINGS);
        }

        $this->listingService->updateListing($listingId, $data);

        foreach ($listing as $variation) {
            if (!$variation['isActive']) continue;

            $status = $variation['failed'] ? $this->itemHelper::SKU_STATUS_INACTIVE : $this->itemHelper::SKU_STATUS_ACTIVE;
            $this->itemHelper->updateVariationSkuStatus($variation['variationId'], $status);
        }
    }
}
