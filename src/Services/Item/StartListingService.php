<?php

namespace Etsy\Services\Item;

use Etsy\Helper\SettingsHelper;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Api\Services\ListingService;
use Etsy\Api\Services\ListingImageService;
use Etsy\Helper\ItemHelper;
use Etsy\Logger\Logger;
use Etsy\Api\Services\ListingTranslationService;

/**
 * Class StartListingService
 */
class StartListingService
{
	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @var ItemHelper
	 */
	private $itemHelper;

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var ListingService
	 */
	private $listingService;

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
	 * @param ItemHelper                $itemHelper
	 * @param ConfigRepository          $config
	 * @param ListingService            $listingService
	 * @param ListingImageService       $listingImageService
	 * @param Logger                    $logger
	 * @param ListingTranslationService $listingTranslationService
	 * @param SettingsHelper            $settingsHelper
	 */
	public function __construct(
		ItemHelper $itemHelper,
		ConfigRepository $config,
		ListingService $listingService,
		ListingImageService $listingImageService,
		Logger $logger,
		ListingTranslationService $listingTranslationService,
		SettingsHelper $settingsHelper
	)
	{
		$this->itemHelper                = $itemHelper;
		$this->config                    = $config;
		$this->logger                    = $logger;
		$this->listingTranslationService = $listingTranslationService;
		$this->listingService            = $listingService;
		$this->listingImageService       = $listingImageService;
		$this->settingsHelper            = $settingsHelper;
	}

	/**
	 * @param Record $record
	 */
	public function start(Record $record)
	{

		if(strlen((string) $record->variationMarketStatus->sku) == 0)
		{
			$listingId = $this->createListing($record);
		}
		else
		{
			$listingId = $record->variationMarketStatus->sku;
		}

		$listingId = $this->createListingMockupResponse();

		if(!is_null($listingId))
		{
			// $this->addPictures($record, $listingId);

			$this->addTranslations($record, $listingId);

			$this->publish($listingId, $record->variationBase->id);
		}
		else
		{
			$this->logger->log('Could not start listing for variation id: ' . $record->variationBase->id);
		}
	}

	/**
	 * @param Record $record
	 * @return int
	 */
	private function createListing(Record $record)
	{
		$data = [
			'state'                => 'inactive',
			'title'                => 'Test', // get title
			'description'          => 'Description', // get description
			'quantity'             => $this->itemHelper->getStock($record),
			'price'                => number_format($record->variationRetailPrice->price, 2),
			'shipping_template_id' => $this->itemHelper->getItemProperty($record, 'shipping_template_id'),
			'who_made'             => $this->itemHelper->getItemProperty($record, 'who_made'),
			'is_supply'            => (string) $this->itemHelper->getItemProperty($record, 'is_supply'),
			'when_made'            => $this->itemHelper->getItemProperty($record, 'when_made'),
			'taxonomy_id'          => '',
			'should_auto_renew'    => false,
			'is_digital'           => false

			// TODO
			// item_weight
			// item_weight_units
			// item_length
			// item_width
			// item_height
			// item_dimensions_unit
			// recipient
			// occasion
			// style
			// currency_code
			// tags
			// materials
			// shop_section_id
			// processing_min
			// processing_max

		];

		return $this->listingService->createListing($this->settingsHelper->getShopSettings('shopLanguage'), $data); // TODO replace all languages with the shop language
	}

	/**
	 * @param Record $record
	 * @param int    $listingId
	 */
	private function addPictures(Record $record, $listingId)
	{
		$list = $this->itemHelper->getImageList($record->variationImageList['all_images']->toArray(), 'normal');

		foreach($list as $image)
		{
			$this->listingImageService->uploadListingImage($listingId, $image);
		}
	}

	/**
	 * @param Record $record
	 * @param int    $listingId
	 */
	private function addTranslations(Record $record, $listingId)
	{
		//TODO add foreach for the itemDescriptionList
		foreach($record as $description) // does not work until you replace with ->itemDescriptionList
		{
			if(is_array($this->settingsHelper->getShopSettings('exportLanguages')) && in_array($description->lang, $this->settingsHelper->getShopSettings('exportLanguages')) && strlen($description->name1) > 0 && strlen($description->description) > 0)
			{
				$this->listingTranslationService->createListingTranslation($listingId, $description, $description->lang);
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

		$response = $this->listingService->updateListing($listingId, $data);

		if($response)
		{
			$this->itemHelper->generateSku($listingId, $variationId);
		}
		else
		{
			// TODO throw exception
		}
	}

	/**
	 * @return int
	 */
	private function createListingMockupResponse()
	{
		return 465564444;
	}
}
