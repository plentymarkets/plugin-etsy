<?php
namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingInventoryService;
use Etsy\Api\Services\ListingTranslationService;
use Etsy\Helper\ImageHelper;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Item\DataLayer\Models\Record;
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
    protected $inventoryService;

    /**
	 * @param ItemHelper                $itemHelper
	 * @param ConfigRepository          $config
	 * @param ListingService            $listingService
	 * @param SettingsHelper            $settingsHelper
	 * @param ListingTranslationService $listingTranslationService
	 * @param Translator                $translator
     * @param ListingInventoryService $inventoryService
	 */
	public function __construct(
	    ItemHelper $itemHelper,
        ConfigRepository $config,
        ListingService $listingService,
        SettingsHelper $settingsHelper,
        ImageHelper $imageHelper,
        ListingTranslationService $listingTranslationService,
        ListingInventoryService $inventoryService,
        Translator $translator)
	{
		$this->config                    = $config;
		$this->settingsHelper            = $settingsHelper;
		$this->itemHelper                = $itemHelper;
		$this->listingService            = $listingService;
		$this->listingTranslationService = $listingTranslationService;
		$this->ListingInventoryService = $inventoryService;
		$this->translator = $translator;
		$this->imageHelper = $imageHelper;
	}

    /**
     * Update the listing
     *
     * @param array $listing
     */
	public function update(array $listing)
	{
	    //todo: an Katalog array anpassen
	    $listingId = $listing['main']['skus'][0]['parentSku'];

	    try {
	        $this->updateListing($listing, $listingId);
	        $this->updateInventory($listing, $listingId);
//	        $this->
        } catch (\Exception $e){

        }

	    /*
		$listingId = $record->variationMarketStatus->sku;

		if(!is_null($listingId))
		{
			try
			{
				$this->addTranslations($record, $listingId);
				
				$this->updateListing($record, $listingId);

				// TODO: Pictures in later sprints
//				$this->addPictures($record, $listingId);

				$this->getLogger(__FUNCTION__)
				     ->addReference('etsyListingId', $listingId)
				     ->addReference('variationId', $record->variationBase->id)
				     ->info('Etsy::item.updateListingSuccess');
			}
			catch(\Exception $ex)
			{
				if(strpos($ex->getMessage(), 'must be active') !== false)
				{
					$this->itemHelper->deleteSku($record->variationMarketStatus->id);

					$this->getLogger(__FUNCTION__)
					     ->addReference('variationId', $record->variationBase->id)
					     ->addReference('etsyListingId', $listingId)
					     ->warning('Etsy::item.skuRemovalSuccess', [
						     'sku' => $record->variationMarketStatus->sku
					     ]);
				}

				if (strpos($ex->getMessage(), 'The listing is not editable, must be active or expired but is removed') !== false)
				{
					$this->getLogger(__FUNCTION__)
						->addReference('variationId', $record->variationBase->id)
						->addReference('etsyListingId', $listingId)
						->error('Etsy::item.startListingErrorInvalidSku', [
							'exception' => $ex->getMessage(),
							'instruction' => $this->translator->trans('Etsy::instructions.instructionInvalidSku')
						]);
				}
				else
				{
					$this->getLogger(__FUNCTION__)
						->addReference('etsyListingId', $listingId)
						->addReference('variationId', $record->variationBase->id)
						->error('Etsy::item.updateListingError', $ex->getMessage());
				}
			}
		}
		else
		{
			$this->getLogger(__FUNCTION__)
				->addReference('etsyListingId', $listingId)
				->addReference('variationId', $record->variationBase->id)
				->info('Etsy::item.updateListingError');
		}
		 */
	}

    /**
     * @param array $listing
     * @param int $listingId
     */
	private function updateListing(array $listing, int $listingId)
	{
	    $data = [];

	    $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');

        //title and description
        foreach ($listing['main']['texts'] as $text) {
            if ($text['lang'] == $language) {
                $data['title'] = str_replace(':', ' -', $text['name1']);
                $data['title'] = ltrim($data['title'], ' +-!?');


                $data['description'] = html_entity_decode(strip_tags($text['description']));
            }
        }

        $boolConvertibleString = ['1', 'y', 'true'];

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


        if (isset($listing['main']['tags'])) {
            $data['tags'] = explode(',', $listing['main']['tags']);
        }

        if (isset($listing['main']['is_private'])){
            $data['is_private'] = ($listing['main']['is_private'] == 1) ? true : false;
        }

        if (isset($listing['main']['materials'])) {
            $data['materials'] = explode(',', $listing['main']['materials']);
        }

        if (isset($listing['main']['style'])){
            $data['style'] = explode(',', $listing['main']['style']);
        }

        if (isset($listing['main']['is_customizable'])) {
            $data['is_customizable'] = (in_array(strtolower($listing['main']['is_customizable']), $boolConvertibleString)) ? true : false;
        }

        if (isset($listing['main']['non_taxable'])) {
            $data['non_taxable'] = (in_array(strtolower($listing['main']['non_taxable']), $boolConvertibleString)) ? true : false;

        if (isset($listing['main']['processing_min'])){
            $data['processing_min'] = $listing['main']['processing_min'];
        }

        if (isset($listing['main']['processing_max'])){
            $data['processing_min'] = $listing['main']['processing_max'];
        }

        if (isset($listing['main']['is_customizable'])) {
            $data['is_customizable'] = (in_array(strtolower($listing['main']['is_customizable']), $boolConvertibleString)) ? true : false;
        }

        if (isset($listing['main']['renew'])){
            $data['renew'] = (in_array(strtolower($listing['main']['renew']), $boolConvertibleString)) ? true : false;
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

        if (isset($listing['main']['shop_section_id'])) {
            $data['shop_section_id'] = $listing['main']['shop_section_id'];
        }

        $response = $this->listingService->updateListing($listingId, $data, $language);

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

        $results = (array)$response['results'];

        return (int)reset($results)['listing_id'];

	}
	}


    /**
     * @param array $listing
     * @param int $listingId
     */
    public function updateInventory(array $listing, int $listingId)
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

    public function updateImages()
    {
        $etsyImages = $this->settingsHelper->get($this->imageHelper::TABLE_NAME);
//        if ($etsyImages)
        //todo Wenn Bilder gelöscht, geändert oder hinzugefügt werden müssen sie über die ID gematched werden um
	}

	/**
	 * Add translations to listing.
	 *
	 * @param Record $record
	 * @param int    $listingId
	 */
	private function addTranslations(Record $record, $listingId)
	{

	    $mainLanguage = $this->settingsHelper->getShopSettings('mainLanguage', 'de');
	    $exportLanguages = $this->settingsHelper->getShopSettings('exportLanguages', [$mainLanguage]);

		foreach($exportLanguages as $language)
		{
			if($language != $mainLanguage && $record->itemDescription[$language]['name1'] && strip_tags($record->itemDescription[$language]['description'])) {
				try
				{
					$title = trim(preg_replace('/\s+/', ' ', $record->itemDescription[ $language ]['name1']));
					$title = ltrim($title, ' +-!?');
					
					$legalInformation = $this->itemHelper->getLegalInformation($language);

					$data = [
                        'title' => $title,
                        'description' => html_entity_decode(strip_tags($record->itemDescription[ $language ]['description'].$legalInformation)),
					];

                    if ($record->itemDescription[$language]['keywords']) {
                        $data['tags'] = $this->itemHelper->getTags($record, $language);
                    }

                    $this->listingTranslationService->updateListingTranslation($listingId, $language, $data);
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
}
