<?php

namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\EtsyServiceProvider;
use Etsy\Exceptions\ListingException;
use Etsy\Validators\EtsyListingValidator;
use Etsy\Helper\ImageHelper;
use Etsy\Helper\SettingsHelper;
use Etsy\Api\Services\ListingService;
use Etsy\Api\Services\ListingImageService;
use Etsy\Helper\ItemHelper;
use Etsy\Api\Services\ListingTranslationService;
use Illuminate\Support\MessageBag;
use Plenty\Modules\Frontend\Contracts\CurrencyExchangeRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationExportServiceContract;
use Plenty\Modules\Item\Variation\Services\ExportPreloadValue\ExportPreloadValue;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;
use Plenty\Exceptions\ValidationException;

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
     * @var Translator
     */
    protected $translator;

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
     * no stock limit
     */
    const NO_STOCK_LIMITATION_ = 0;

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
        CurrencyExchangeRepositoryContract $currencyExchangeRepository,
        Translator $translator
    ) {
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
        $this->translator = $translator;
    }

    /**
     * Start the listing
     *
     * @param array $listing
     * @throws \Exception
     */
    public function start(array $listing)
    {
        if (!isset($listing['main'])) {
            $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                ->addReference('itemId', $listing['main']['itemId'])
                ->error($this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.startListingError'),
                    $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.noMainVariation'));
            return;
        }

        try {
            $listingData = $this->createListing($listing);
            $listing = $listingData['listing'];
            unset($listingData['listing']);
        } catch (ListingException $listingException) {
            $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                ->addReference('itemId', $listing['main']['itemId'])
                ->error($listingException->getMessage(), $listingException->getMessageBag());
            return;
        } catch (ValidationException $validationException) {
            $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                ->addReference('itemId', $listing['main']['itemId'])
                ->error($validationException->getMessage(), $validationException->getMessageBag());
            return;
        } catch (\Exception $exception) {
            $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                ->addReference('itemId', $listing['main']['itemId'])
                ->error($exception->getMessage());
            return;
        }

        $listingId = (int)$listing['main']['listingId'];

        try {
            $this->addTranslations($listing, $listingId);
            $listing = $this->fillInventory($listingId, $listing);
            $this->addPictures($listingId, $listing);
            $this->publish($listingId, $listing, $listingData['etsyListing']);
        } catch (ListingException $listingException) {
            $skus = [];
            foreach ($listing as $variation) {
                $sku = $this->itemHelper->getVariationSku($variation['variationId']);
                if ($sku) {
                    $skus[$variation['variationId']] = $sku->sku;
                }
            }

            if (count($skus)) {
                $this->itemHelper->deleteListingsSkus($listingId, $this->settingsHelper
                    ->get($this->settingsHelper::SETTINGS_ORDER_REFERRER));

                $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                    ->addReference('itemId', $listing['main']['itemId'])
                    ->addReference('etsyListingId', $listingId)
                    ->report(EtsyServiceProvider::PLUGIN_NAME . '::item.skuRemovalSuccess', $skus);
            }

            $this->listingService->deleteListing($listingId);

            $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                ->addReference('itemId', $listing['main']['itemId'])
                ->addReference('etsyListingId', $listingId)
                ->error($listingException->getMessage(), $listingException->getMessageBag());
        } catch (\Exception $exception) {
            $skus = [];
            foreach ($listing as $variation) {
                /** @var VariationSku $sku */
                $sku = $this->itemHelper->getVariationSku($variation['variationId']);
                if ($sku) {
                    $skus[$variation['variationId']] = $sku->sku;
                }
            }

            if (count($skus)) {
                $this->itemHelper->deleteListingsSkus($listingId, $this->settingsHelper
                    ->get($this->settingsHelper::SETTINGS_ORDER_REFERRER));

                $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                    ->addReference('itemId', $listing['main']['itemId'])
                    ->addReference('etsyListingId', $listingId)
                    ->report(EtsyServiceProvider::PLUGIN_NAME . '::item.skuRemovalSuccess', $skus);
            }

            $this->listingService->deleteListing($listingId);

            $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                ->addReference('itemId', $listing['main']['itemId'])
                ->addReference('etsyListingId', $listingId)
                ->error($exception->getMessage());
        }
    }

    /**
     * @param array $listing
     * @return array
     * @throws ListingException
     * @throws \Plenty\Exceptions\ValidationException
     */
    protected function createListing(array $listing)
    {
        $data = [];
        $failedVariations = [];
        $variationExportService = $this->variationExportService;
//        the validator makes the cron fail at some point need to check that asap
//        EtsyListingValidator::validateOrFail($listing['main']);

        $data['state'] = 'draft';

        $mainLanguage = $this->settingsHelper->getShopSettings('mainLanguage');

        //loading etsy currency
        $shops = json_decode($this->settingsHelper->get($this->settingsHelper::SETTINGS_ETSY_SHOPS), true);
        $etsyCurrency = reset($shops)['currency_code'];

        //loading default currency
        $defaultCurrency = $this->currencyExchangeRepository->getDefaultCurrency();

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

        //quantity & price
        $data['quantity'] = 0;
        $hasActiveVariations = false;

        $variationExportService->addPreloadTypes([$variationExportService::STOCK]);
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

            if (!isset($variation['sales_price']) || (float)$variation['sales_price'] <= self::MINIMUM_PRICE) {
                $listing[$key]['failed'] = true;
                $failedVariations['variation-' . $variation['variationId']][] = $this->translator
                    ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.variationPriceMissing');
                continue;
            }

            // todo reactivate this feature when we have a solution for shipping time depending on quantity sold
//            if ($variation['stockLimitation'] === self::NO_STOCK_LIMITATION_) {
//                $data['quantity'] = UpdateListingStockService::MAXIMUM_ALLOWED_STOCK;
//            } else {
//                if (!isset($stock) || $stock[0]['stockNet'] < 1) {
//                    $listing[$key]['failed'] = true;
//                    $failedVariations['variation-' . $variation['variationId']][] = $this->translator
//                        ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.variationNoStock');
//                    continue;
//                }
//
//                if ($listing[$key]['failed']) {
//                    continue;
//                }
//
//
//
//                $data['quantity'] += (int)$stock[0]['stockNet'];
//            }

            //Only throw an error if the stock did not get loaded. If there just is no stock thats fine since it won't
            //be purchasable
            if (!isset($stock)) {
                $listing[$key]['failed'] = true;
                $failedVariations['variation-' . $variation['variationId']][] = $this->translator
                    ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.variationNoStock');
                continue;
            }

            if ($listing[$key]['failed']) {
                continue;
            }


            $data['quantity'] += (int)$stock[0]['stockNet'] >= 0 ? (int)$stock[0]['stockNet'] : 0;

            if (!isset($data['price']) || $data['price'] > $variation['sales_price']) {
                if ($defaultCurrency == $etsyCurrency) {
                    $data['price'] = (float)$variation['sales_price'];
                } else {
                    $data['price'] = $this->currencyExchangeRepository->convertFromDefaultCurrency($etsyCurrency,
                        (float)$variation['sales_price'],
                        $this->currencyExchangeRepository->getExchangeRatioByCurrency($etsyCurrency));
                    $data['price'] = round($data['price'], self::MONEY_DECIMALS);
                }
            }

            $hasActiveVariations = true;
        }

        if ($data['quantity'] > UpdateListingStockService::MAXIMUM_ALLOWED_STOCK){
            $data['quantity'] = UpdateListingStockService::MAXIMUM_ALLOWED_STOCK;
        }

        //shipping profiles
        $data['shipping_template_id'] = (int)reset($listing['main']['shipping_profiles']);

        $data['who_made'] = $listing['main']['who_made'];
        $data['is_supply'] = in_array(strtolower($listing['main']['is_supply']),
            self::BOOL_CONVERTIBLE_STRINGS);
        $data['when_made'] = $listing['main']['when_made'];

        //Category
        $data['taxonomy_id'] = (int)reset($listing['main']['categories']);

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
            $materialCounter = 0;

            foreach ($materials as $key => $material) {
                if ($materialCounter > 13) {
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
                $materialCounter++;
            }

            if ($materialCounter > 0) {
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

        $articleFailed = false;
        $articleErrors = [];

        //logging article errors
        if (!$hasActiveVariations || $data['quantity'] <= 0) {
            $articleFailed = true;
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.noVariations');
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

        if (count($listing['main']['attributes']) > 2) {
            $articleFailed = true;
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.tooManyAttributes');
        }

        if (!isset($data['price'])) {
            $articleFailed = true;
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.missingPrice');
        }

        if ($data['quantity'] <= 0) {
            $articleFailed = true;
            $articleErrors[] = $this->translator
                ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.noStock');
        }

        //Error handling
        if ($articleFailed || count($failedVariations)) {
            $exceptionMessage = ($articleFailed) ? '::log.articleNotListable' : '::log.variationsNotListed';

            foreach ($failedVariations as $variationId => $variationErrors) {
                $failedVariations[$variationId] = implode(",\n", $variationErrors);
            }

            if ($articleFailed) {
                $errors = array_merge($articleErrors, $failedVariations);
                $messageBag = pluginApp(MessageBag::class, ['messages' => $errors]);
                throw new ListingException($messageBag, EtsyServiceProvider::PLUGIN_NAME . $exceptionMessage);
            }

            $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
                ->addReference('itemId', $listing['main']['itemId'])
                ->error(EtsyServiceProvider::PLUGIN_NAME . $exceptionMessage, $failedVariations);
        }

        //Gotta put the language into the data array, otherwise etsy enums can cause the export to fail
        $data['language'] = $mainLanguage;
        $response = $this->listingService->createListing($mainLanguage, $data);

        //Due to the api issue we need to reuse the listings data in the final update request
        //But in there some attributes are not allowed so we unset them
        unset($data['price']);
        unset($data['quantity']);

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

        $results = (array)$response['results'];
        $listing['main']['listingId'] = (int)reset($results)['listing_id'];

        return ['listing' => $listing, 'etsyListing' => $data];
    }

    /**
     * Creates variations for the listing
     *
     * @param $listingId
     * @param $listing
     * @throws ListingException
     */
    protected function fillInventory($listingId, $listing)
    {
        $mainLanguage = $this->settingsHelper->getShopSettings('mainLanguage');
        $variationExportService = $this->variationExportService;
        $products = [];
        $dependencies = [];

        //loading etsy currency
        $shops = json_decode($this->settingsHelper->get($this->settingsHelper::SETTINGS_ETSY_SHOPS), true);
        $etsyCurrency = reset($shops)['currency_code'];

        //loading default currency
        $defaultCurrency = $this->currencyExchangeRepository->getDefaultCurrency();

        foreach ($listing as $variation) {
            if (!count($variation['attributes'])) {
                continue;
            }
            if (count($variation['attributes']) > 2) {
                $this->getLogger(EtsyServiceProvider::PLUGIN_NAME)
                    ->addReference('variationId', $variation['variationId'])
                    ->error('Etsy only allows 2 attributes');
            }
            if (isset($variation['attributes'][0])) {
                $attributeOneId = $variation['attributes'][0]['attributeId'];
                $dependencies[] = $this->inventoryService::CUSTOM_ATTRIBUTE_1;
            }

            if (isset($variation['attributes'][1])) {
                $attributeTwoId = $variation['attributes'][1]['attributeId'];
                $dependencies[] = $this->inventoryService::CUSTOM_ATTRIBUTE_2;
            }
            break;
        }

        //Some customers use the main variation just as a container so it has no attributes. If it is still active
        //it has to be filtered out at this point
        if (count($listing['main']['attributes']) < count($dependencies)) {
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
        $hasActiveVariations = false;
        $counter = 0;

        foreach ($listing as $key => $variation) {
            if (!$variation['isActive']) {
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
                    if ($name['lang'] == $mainLanguage) {
                        $attributeName = $name['name'];
                    }
                }

                foreach ($attribute['value']['names'] as $name) {
                    if ($name['lang'] == $mainLanguage) {
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
                        'property_id' => $this->inventoryService::CUSTOM_ATTRIBUTE_1,
                        'property_name' => $attributeName,
                        'values' => [$attributeValueName]
                    ]);
                } elseif (isset($attributeTwoId) && $attribute['attributeId'] == $attributeTwoId) {
                    //Second attribute needs to be at the end of the array
                    array_push($products[$counter]['property_values'], [
                        'property_id' => $this->inventoryService::CUSTOM_ATTRIBUTE_2,
                        'property_name' => $attributeName,
                        'values' => [$attributeValueName]
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
                    'quantity' => (int)$quantity >= 0 ? (int)$quantity : 0,
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
                array_unshift($failedVariations, $this->translator
                    ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.noVariations'));
                $messageBag = pluginApp(MessageBag::class, ['messages' => $failedVariations]);
                throw new ListingException($messageBag, $exceptionMessage);
            }

            $this->getLogger(EtsyServiceProvider::START_LISTING_INVENTORY)
                ->addReference('itemId', $listing['main']['itemId'])
                ->addReference('etsyListingId', $listingId)
                ->error($exceptionMessage, $failedVariations);
        }

        $data = [
            'products' => json_encode($products),
            'price_on_property' => $dependencies,
            'quantity_on_property' => $dependencies,
            'sku_on_property' => $dependencies
        ];

        $response = $this->inventoryService->updateInventory($listingId, $data, $mainLanguage);

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

        return $listing;
    }

    /**
     * Add pictures to listing.
     *
     * @param int $listingId
     * @param $listing
     * @throws ListingException
     */
    protected function addPictures($listingId, $listing)
    {
        $orderReferrer = $this->settingsHelper->get($this->settingsHelper::SETTINGS_ORDER_REFERRER);
        if (!isset($listing['main']['images']['all'])) {
            $messageBag = pluginApp(MessageBag::class, [
                'messages' => [
                    $this->translator
                        ->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.noImages')
                ]
            ]);
            throw new ListingException($messageBag,
                $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.startListingError'));
        }
        $list = $listing['main']['images']['all'];
        $newList = [];
        foreach ($list as $key => $image) {
            foreach ($image['availabilities']['market'] as $availability) {
                if ($availability === -1) {
                    $newList[] = $image;
                }
                if ($availability != $orderReferrer) {
                    unset($list[$key]);
                } else {
                    $newList[] = $image;
                }
            }
        }
        $sortedList = $this->imageHelper->sortImagePosition($newList);
        $imageList = [];
        $slicedList = array_slice($sortedList, 0, 10);
        foreach ($slicedList as $image) {
            $response = $this->listingImageService->uploadListingImage($listingId, $image['url'], $image['position']);
            if (!isset($response['results']) || !is_array($response['results'])
                || !isset($response['results'][0]) || !isset($response['results'][0]['listing_image_id'])) {
                if (is_array($response) && isset($response['error_msg'])) {
                    $message = $response['error_msg'];
                } else {
                    if (is_string($response)) {
                        $message = $response;
                    } else {
                        $message = $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.emptyResponse');
                    }
                }
                $this->getLogger(EtsyServiceProvider::UPLOAD_LISTING_IMAGE)
                    ->addReference('imageId', $image['id'])
                    ->warning($this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.imageFailed'),
                        $message);
            }
            $imageList[] = [
                'imageId' => $image['id'],
                'listingImageId' => $response['results'][0]['listing_image_id'],
                'listingId' => $response['results'][0]['listing_id'],
                'itemId' => $image['itemId'],
                'imageUrl' => $image['url']
            ];
        }
        if (!count($imageList)) {
            $messageBag = pluginApp(MessageBag::class, [
                'messages' =>
                    [$this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::log.noImages')]
            ]);
            throw new ListingException($messageBag,
                $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME . '::item.startListingError'));
        }
        $this->imageHelper->save($listingId, json_encode($imageList));
    }


    /**
     * @param array $listing
     * @param $listingId
     * @throws \Exception
     */
    protected function addTranslations(array $listing, $listingId)
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
                ->addReference('listingId', $listingId)
                ->info('No more export languages activated except the main language');
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

            $response = $this->listingTranslationService->createListingTranslation($listingId, strtolower($translatableLanguage), $data);

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

    /**
     * @param int $listingId
     * @param $listing
     * @param $etsyListingData
     * For some reason we need to send the entire listing currently in an update or the request fails, therefore we need
     * the listing data at this point
     */
    protected function publish($listingId, $listing, $etsyListingData)
    {
        $etsyListingData['state'] = 'active';
        $language = "";

        if (isset($etsyListingData['language'])) {
            $language = $etsyListingData['language'];
            unset($etsyListingData['language']);
        }

        $this->listingService->updateListing($listingId, $etsyListingData, $language);

        foreach ($listing as $variation) {
            if (!$variation['isActive'] || $variation['failed']) {
                continue;
            }

            $status = $this->itemHelper::SKU_STATUS_ACTIVE;
            $this->itemHelper->updateVariationSkuStatus($variation['variationId'], $status);
        }
    }
}
