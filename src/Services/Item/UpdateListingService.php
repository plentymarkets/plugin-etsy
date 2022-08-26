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
        if (!isset($listing['main'])) {
            $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                ->error($this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.updateListingError'),
                    $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.noMainVariation'));
            return;
        }

        $listingId = 0;

        foreach ($listing as $variation) {
            if (isset($variation['skus'][0]['parentSku'])) {
                $listingId = (int)$variation['skus'][0]['parentSku'];
                break;
            }
        }

        $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_SERVICE)
            ->addReference('itemId', $listing['main']['itemId'])
            ->report(EtsyServiceProvider::PLUGIN_NAME . '::log.startToUpdateListing', ['listing' => $listing, 'listingId' => $listingId]);

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
//        the validator makes the cron fail at some point need to check that asap
//        EtsyListingValidator::validateOrFail($listing['main']);

        $mainLanguage = $this->settingsHelper->getShopSettings('mainLanguage');

        //legal information
        $legalInformation = $this->itemHelper->getLegalInformation($mainLanguage);

        $catalogTitle = 'title' . strtoupper($mainLanguage);

        if (isset($listing['main'][$catalogTitle])) {
            $data['title'] = str_replace(':', ' -', $listing['main'][$catalogTitle]);
            $data['title'] = ltrim($data['title'], ' +-!?');
        } else {
            foreach ($listing['main']['texts'] as $text) {
                if ($text['lang'] == $mainLanguage) {
                    $data['title'] = str_replace(':', ' -', $text['name1']);
                    $data['title'] = ltrim($data['title'], ' +-!?');
                }
            }
        }

        $catalogDescription = 'description' . strtoupper($mainLanguage);
        if (isset($listing['main'][$catalogDescription])) {
            $data['description'] = $listing['main'][$catalogDescription] . $legalInformation;
        } else {
            foreach ($listing['main']['texts'] as $text) {
                if ($text['lang'] == $mainLanguage) {
                    $data['description'] = $text['description'] . $legalInformation;
                    break;
                }
            }
        }

        //converting html line breaks into \n and removing all persisting html tags
        $data['description'] = str_replace("<br>", "\n", $data['description']);
        $data['description'] = str_replace("<br />", "\n", $data['description']);
        $data['description'] = html_entity_decode(strip_tags($data['description']));

        $hasActiveVariations = false;

        foreach ($listing as $key => $variation) {
            if (!$variation['isActive']) {
                continue;
            }

            $listing[$key]['failed'] = false;

            if (!isset($variation['sales_price'])) {
                $listing[$key]['failed'] = true;
                $failedVariations[$variation['variationId']][] = $this->translator
                    ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.variationPriceMissing');
                continue;
            }

            $hasActiveVariations = true;
        }

        //shipping profiles
        $data['shipping_template_id'] = isset($listing['main']['shipping_profiles']) && is_array($listing['main']['shipping_profiles']) ? (int)reset($listing['main']['shipping_profiles']) : 0;

        $data['who_made'] = $listing['main']['who_made'];
        $data['is_supply'] = in_array(strtolower($listing['main']['is_supply']),
            self::BOOL_CONVERTIBLE_STRINGS);
        $data['when_made'] = $listing['main']['when_made'];

        if ($data['when_made'] == "2020_2020") {
            // etsy changed this value. We adapt it here to keep customer configuration working
            $data['when_made'] = "2020_2022";
        }

        //Category
        $data['taxonomy_id'] = isset($listing['main']['categories']) && is_array($listing['main']['categories']) ? (int)reset($listing['main']['categories']) : 0;

        //Etsy properties
        $catalogTag = 'tags' . strtoupper($mainLanguage);

        //Etsy properties
        if (isset($listing['main'][$catalogTag]) && $listing['main'][$catalogTag] != "") {
            $tags = explode(',', $listing['main'][$catalogTag]);
            $tagCounter = 0;

            foreach ($tags as $key => $tag) {
                if ($tagCounter > 13) {
                    break;
                }


                $data['tags'][] = $tag;
                $tagCounter++;
            }

            if ($tagCounter > 0) {
                $data['tags'] = implode(',', $data['tags']);
            }
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

        if (isset($listing['main']['shopSections'][0])) {
            $data['shop_section_id'] = (int)$listing['main']['shopSections'][0];
        }

        if (isset($listing['main']['materials'])) {
            $materials = explode(',', $listing['main']['materials']);
            $counter = 0;

            foreach ($materials as $key => $material) {
                if ($counter > 13) {
                    break;
                }

                if (preg_match('@[^\p{L}\p{Nd}\p{Zs}]@u', $material) > 0 || $material == "") {
                    $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                        ->addReference('itemId', $listing['main']['itemId'])
                        ->warning(EtsyServiceProvider::PLUGIN_NAME . '::log.wrongMaterialFormat',
                            [$listing['main']['materials'], $material]);
                    continue;
                }

                $data['materials'][] = $material;
                $counter++;
            }

            if ($counter > 0) {
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
            $data['processing_min'] = (int) $listing['main']['processing_min'];
        }

        if (isset($listing['main']['processing_max'])) {
            $data['processing_max'] = (int) $listing['main']['processing_max'];
        }

        if (isset($listing['main']['style']) && is_string($listing['main']['style'])) {
            $styles = explode(',', $listing['main']['style']);
            $counter = 0;

            foreach ($styles as $style) {
                if ($counter > 1) {
                    break;
                }

                if (preg_match('@[^\p{L}\p{Nd}\p{Zs}]@u', $style) > 0 || $style == "") {
                    $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                        ->addReference('itemId', $listing['main']['itemId'])
                        ->warning(EtsyServiceProvider::PLUGIN_NAME . '::log.wrongStyleFormat',
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

        if (isset($listing['main']['shop_section_id'][0])) {
            $data['shop_section_id'] = (int)$listing['main']['shop_section_id'];
        }

        $articleFailed = false;
        $articleErrors = [];

        //logging article errors
        if (!$hasActiveVariations) {
            $articleFailed = true;
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.noVariations');
            $this->listingService->updateListing($listingId, ['state' => 'inactive'], $mainLanguage);
        }

        if ((!isset($data['title']) || $data['title'] == '')
            || (!isset($data['description']) || $data['description'] == '')) {
            $articleFailed = true;
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.wrongTitleOrDescription');
        }

        if (mb_strlen($data['title']) > 140) {
            $articleFailed = true;
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.longTitle');
        }

        if (is_array($listing['main']['attributes']) && count($listing['main']['attributes']) > 2) {
            $articleFailed = true;
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.tooManyAttributes');
        }

        //Logging failed variations
        foreach ($failedVariations as $id => $errors) {
            $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_SERVICE)
                ->addReference('variationId', $id)
                ->error(EtsyServiceProvider::PLUGIN_NAME . '::log.', $errors);
        }

        if ($articleFailed || (is_array($failedVariations) && count($failedVariations))) {
            $exceptionMessage = ($articleFailed) ? '::log.articleNotListable' : '::log.variationsNotListed';

            foreach ($failedVariations as $variationId => $variationErrors) {
                $failedVariations[$variationId] = implode(",\n", $variationErrors);
            }

            if ($articleFailed) {
                $errors = array_merge($articleErrors, $failedVariations);
                $messageBag = pluginApp(MessageBag::class, ['messages' => $errors]);
                throw new ListingException($messageBag, EtsyServiceProvider::PLUGIN_NAME . $exceptionMessage);
            }

            $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_SERVICE)
                ->addReference('itemId', $listing['main']['itemId'])
                ->error(EtsyServiceProvider::PLUGIN_NAME . $exceptionMessage, $failedVariations);
        }

        $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_SERVICE)
            ->addReference('etsyListingId',$listingId)
            ->report(EtsyServiceProvider::PLUGIN_NAME, $data);

        $response = $this->listingService->updateListing($listingId, $data, $mainLanguage);

        $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_SERVICE)
            ->addReference('etsyListingId',$listingId)
            ->report(EtsyServiceProvider::PLUGIN_NAME, [
                'data' => $data,
                'result' => json_encode($response)
            ]);

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

        $isSingleListing = false;

        if (is_array($listing) && count($listing) == 1) {
            $isSingleListing = true;
        }

        foreach ($listing as $key => $variation) {
            if ($key == 'main' && !$isSingleListing) {
                continue;
            }
            if (!is_null($variation['attributes']) && (is_array($variation['attributes']) && !count($variation['attributes']))) {
                continue;
            }
            if (is_array($variation['attributes']) && count($variation['attributes']) > 2) {
                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $variation['variationId'])
                    ->error('Etsy only allows 2 attributes');
            }
            if (isset($variation['attributes'][0])) {
                $attributeOneId = $variation['attributes'][0]['attributeId'];
                $dependencies[] = $this->listingInventoryService::CUSTOM_ATTRIBUTE_1;
            }
            if (isset($variation['attributes'][1])) {
                $attributeTwoId = $variation['attributes'][1]['attributeId'];
                $dependencies[] = $this->listingInventoryService::CUSTOM_ATTRIBUTE_2;
            }
            break;
        }

        //Some customers use the main variation just as a container so it has no attributes. If it is still active
        //it has to be filtered out at this point
        if ((is_null($listing['main']['attributes']) && count($dependencies) != 0) || (is_array($listing['main']['attributes']) && (count($listing['main']['attributes']) < count($dependencies)))) {
            $listing['main']['failed'] = true;
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
        $isEveryVariationDisabled = false;
        $counter = 0;
        $isEnabled = false;
        $disabledCounter = 0;

        foreach ($listing as $key => $variation) {

            if (!$variation['isActive'] && !isset($variation['skus'][0]['sku'])) {
                continue;
            }

            if (isset($variation['failed']) && $variation['failed']) {
                continue;
            }

            //todo reactivate this feature when we have a solution for shipping time depending on quantity sold
//            if ($variation['stockLimitation'] === StartListingService::NO_STOCK_LIMITATION_) {
//                $quantity = UpdateListingStockService::MAXIMUM_ALLOWED_STOCK;
//            } else {
//                $variationExportService->preload($exportPreloadValueList);
//                $stock = $variationExportService->getAll($variation['variationId']);
//                $stock = $stock[$variationExportService::STOCK];
//                $quantity = $stock[0]['stockNet'];
//            }

            $variationExportService->preload($exportPreloadValueList);
            $stock = $variationExportService->getAll($variation['variationId']);
            $stock = $stock[$variationExportService::STOCK];
            $quantity = $stock[0]['stockNet'] > UpdateListingStockService::MAXIMUM_ALLOWED_STOCK
                ? UpdateListingStockService::MAXIMUM_ALLOWED_STOCK : $stock[0]['stockNet'];

            if ($quantity === 0 && !$variation['isActive']) {
                $isEnabled = false;
            } elseif ($quantity === 0 && $variation['isActive']) {
                $isEnabled = false;
            } elseif ($quantity >= 1 && $variation['isActive']) {
                $isEnabled = true;
            }

            //initialising property values array for articles with no attributes (single variation)
            $products[$counter]['property_values'] = [];

            $attributes = $variation['attributes'];

            /**
             * @var array $attributes
             */
            foreach ($attributes as $attribute) {
                /**
                 * @var array $attribute ['attribute']
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
                        ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.attributeNameMissing');
                    continue 2;
                }

                if (!isset($attributeValueName)) {
                    $variation['failed'] = true;
                    $failedVariations['variation-' . $variation['variationId']][] = $this->translator
                        ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.attributeValueNameMissing');
                    continue 2;
                }

                if (isset($attributeOneId) && $attribute['attributeId'] == $attributeOneId) {
                    //First attribute needs to be at the start of the array
                    array_unshift($products[$counter]['property_values'], [
                        'property_id' => $this->listingInventoryService::CUSTOM_ATTRIBUTE_1,
                        'property_name' => $attributeName,
                        'values' => [$attributeValueName],
                    ]);
                } elseif (isset($attributeTwoId) && $attribute['attributeId'] == $attributeTwoId) {
                    //Second attribute needs to be at the end of the array
                    array_push($products[$counter]['property_values'], [
                        'property_id' => $this->listingInventoryService::CUSTOM_ATTRIBUTE_2,
                        'property_name' => $attributeName,
                        'values' => [$attributeValueName],
                    ]);
                }
            }

            if ($defaultCurrency == $etsyCurrency) {
                $price = (float)$variation['sales_price'];
            } else {
                $price = $this->currencyExchangeRepository->convertFromDefaultCurrency($etsyCurrency,
                    (float)$variation['sales_price'],
                    $this->currencyExchangeRepository->getExchangeRatioByCurrency($etsyCurrency));
                $price = round($price, self::MONEY_DECIMALS);
            }

            if (!$this->itemHelper->updateVariationSkuTimestamp($variation['variationId'])) {
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
                    'quantity' => (int) $quantity >= 0 ? (int) $quantity : 0,
                    'is_enabled' => $isEnabled
                ]
            ];

            $products[$counter]['offerings'][0]['price'] = $price;

            if (!$variation['isActive']) {
                $disabledCounter++;
            }
            $counter++;
        }

        if ($counter === $disabledCounter) {
            $isEveryVariationDisabled = true;
        }

        if (!$isEnabled && $isSingleListing) {
            $this->listingService->updateListing($listingId, ['state' => 'inactive'], $language);
            throw new \Exception("Single variation listing disabled. Variation not active or no stock.");
        }

        //logging failed article / variations
        if ($isEveryVariationDisabled || (is_array($failedVariations) && count($failedVariations))) {
            $exceptionMessage = ($isEveryVariationDisabled) ? 'log.articleNotListable' : 'log.variationsNotListed';

            foreach ($failedVariations as $variationId => $variationErrors) {
                $failedVariations[$variationId] = implode(",\n", $variationErrors);
            }

            if ($isEveryVariationDisabled) {
                $this->listingService->updateListing($listingId, ['state' => 'inactive'], $language);
                array_unshift($failedVariations, $this->translator
                    ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.noVariations'));
                $messageBag = pluginApp(MessageBag::class, ['messages' => $failedVariations]);
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
                    } catch (\Exception $ex) {
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
     * @throws \Exception
     */
    public function updateImages($listing, $listingId)
    {
        $orderReferrer = $this->settingsHelper->get($this->settingsHelper::SETTINGS_ORDER_REFERRER);
        $alreadyListedImages = json_decode($this->imageHelper->get((string)$listingId), true);
        $etsyImages = $this->listingImageService->getListingImages($listingId);
        $deletableListings = [];

        $etsyImages = $etsyImages['results'] ?? []; // no result means no available images, so we use an empty array

        $imageList = [];
        $list = $listing['main']['images']['all'];
        $newList = [];
        foreach ($list as $key => $image) {
            foreach ($image['availabilities']['market'] as $availability) {
                if ($availability == -1 || $availability == $orderReferrer) {
                    $newList[] = $image;
                    break;
                }
            }
        }
		$sortedList = $this->imageHelper->sortImagePosition($newList);
        $slicedList = array_slice($sortedList, 0, 10);

        $imageCounter = 0;

        foreach ($etsyImages as $etsyImageKey => $etsyImage) {
            foreach ($alreadyListedImages as $alreadyListedImageKey => $alreadyListedImage) {
                $isImageStillAvailable = false;
                $isImageFromPlenty = $alreadyListedImage['listingImageId'] == $etsyImage['listing_image_id'];

                foreach ($slicedList as $plentyKey => $plentyImage) {
                    if ( // Image is already correctly listed, so we don't need to work with it anymore
                        $plentyImage['id'] == $alreadyListedImage['imageId']
                        && $isImageFromPlenty
                        && $plentyImage['position'] == $alreadyListedImage['position']
                    ) {
                        $isImageStillAvailable = true;
                        $imageList[] = $alreadyListedImage;
                        unset($slicedList[$plentyKey]);
                        unset($alreadyListedImages[$alreadyListedImageKey]);
                        unset($etsyImages[$etsyImageKey]);
                        $imageCounter++;
                    }
                }

                if (!$isImageStillAvailable && $isImageFromPlenty) { // Image originated in plenty but is not linked anymore
                    $deletableListings[] = $etsyImage['listing_image_id'];
                    unset($alreadyListedImages[$alreadyListedImageKey]);
                    unset($etsyImages[$etsyImageKey]);
                }
            }
        }

        $totalCounter = count($slicedList) + $imageCounter + count($etsyImages);

        if ($totalCounter == 0) {
            throw new \Exception($this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.noImages'));
        }

        if ($totalCounter > 10) { // With the plenty images we will overflow the allowed images so we need to delete non-plenty images
            foreach ($etsyImages as $etsyImageKey => $etsyImage) {
                $deletableListings[] = $etsyImage['listing_image_id'];
                unset($etsyImages[$etsyImageKey]);
                $totalCounter--;

                if ($totalCounter == 10) {
                    break;
                }
            }
        }

        $this->getLogger(EtsyServiceProvider::DELETE_LISTING_IMAGE)
            ->addReference('etsyListingId', $listingId)
            ->addReference('itemId', $listing['main']['itemId'])
            ->debug(EtsyServiceProvider::PLUGIN_NAME . '::log.deleteImage', [
                'deletingImages' => $deletableListings,
                'newImages' => $slicedList
            ]);

        foreach ($deletableListings as $deletableListingId) {
            try {
                $response = $this->listingImageService->deleteListingImage($listingId,
                    $deletableListingId);
            } catch (\Exception $exception) {
                //This exception will be thrown if the image does not exist anymore. We were about to delete it so it doesnt matter
                if ($exception->getMessage() != 'The listing_id provided does not belong to the same shop that is associated with the listing_image_id.') {
                    throw $exception;
                }
            }

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

        foreach ($slicedList as $image) {
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
                'position' => $image['position'],
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
    public function addTranslations(array $listing, $listingId)
    {
        $mainLanguage = $this->settingsHelper->getShopSettings('mainLanguage');
        $activatedExportLanguages = $this->settingsHelper->getShopSettings('exportLanguages');

        $translatableLanguages = [];

        foreach ($activatedExportLanguages as $activatedExportLanguage) {
            if ($activatedExportLanguage !== $mainLanguage) {
                $translatableLanguages[] = $activatedExportLanguage;
            }
        }

        if (empty($translatableLanguages)) {
            $this->getLogger(EtsyServiceProvider::LISTING_TRANSLATIONS)
                ->addReference('etsyListingId', $listingId)
                ->info(EtsyServiceProvider::PLUGIN_NAME . '::log.languageMissing');
            return;
        }

        foreach ($translatableLanguages as $translatableLanguage) {
            $data = [];

            $catalogTitle = 'title' . strtoupper($translatableLanguage);

            if (isset($listing['main'][$catalogTitle])) {
                $data['title'] = str_replace(':', ' -', $listing['main'][$catalogTitle]);
                $data['title'] = ltrim($data['title'], ' +-!?');
            }

            $catalogDescription = 'description' . strtoupper($translatableLanguage);
            if (isset($listing['main'][$catalogDescription])) {
                $data['description'] = html_entity_decode(strip_tags(str_replace
                ("<br />", "\n", $listing['main'][$catalogDescription])));
            }

            $catalogTag = 'tags' . strtoupper($translatableLanguage);

            //Etsy properties
            if (isset($listing['main'][$catalogTag]) && $listing['main'][$catalogTag] != "") {
                $tags = explode(',', $listing['main'][$catalogTag]);
                $tagCounter = 0;

                foreach ($tags as $key => $tag) {
                    if ($tagCounter > 13) {
                        break;
                    }


                    $data['tags'][] = $tag;
                    $tagCounter++;
                }

                if ($tagCounter > 0) {
                    $data['tags'] = implode(',', $data['tags']);
                }
            }

            if (!count($data)) {
                //There are no translations for the given listing todo: log
                return;
            }

            $response = $this->listingTranslationService->updateListingTranslation($listingId,
                strtolower($translatableLanguage), $data);

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
                    $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.startListingError'));
            }
        }
    }

    public function publish($listingId, $listing)
    {
        $data = [
            'state' => 'active',
        ];

        $this->listingService->updateListing($listingId, $data);

        foreach ($listing as $variation) {
            $status = $variation['failed'] ? $this->itemHelper::SKU_STATUS_ERROR : $this->itemHelper::SKU_STATUS_ACTIVE;
            $this->itemHelper->updateVariationSkuStatus($variation['variationId'], $status);
        }
    }
}
