<?php

namespace Etsy\Services\Item;

use Etsy\Helper\SettingsHelper;
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
	 * @param ListingService            $listingService
	 * @param ListingImageService       $listingImageService
	 * @param Logger                    $logger
	 * @param ListingTranslationService $listingTranslationService
	 * @param SettingsHelper            $settingsHelper
	 */
	public function __construct(ItemHelper $itemHelper, ListingService $listingService, ListingImageService $listingImageService, Logger $logger, ListingTranslationService $listingTranslationService, SettingsHelper $settingsHelper)
	{
		$this->itemHelper                = $itemHelper;
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
		$listingId = $this->createListing($record);

		if(!is_null($listingId))
		{
			$this->addPictures($record, $listingId);

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
	 *
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
			'shipping_template_id' => $this->itemHelper->getShippingTemplateId($record),
			'who_made'             => $this->itemHelper->getProperty($record, 'who_made', $this->settingsHelper->getShopSettings('mainLanguage')),
			'is_supply'            => $this->itemHelper->getProperty($record, 'is_supply', $this->settingsHelper->getShopSettings('mainLanguage')),
			'occasion'             => $this->itemHelper->getProperty($record, 'is_supply', $this->settingsHelper->getShopSettings('mainLanguage')),
			'recipient'            => $this->itemHelper->getProperty($record, 'is_supply', $this->settingsHelper->getShopSettings('mainLanguage')),
			'when_made'            => $this->itemHelper->getProperty($record, 'when_made', $this->settingsHelper->getShopSettings('mainLanguage')),
			'taxonomy_id'          => $this->itemHelper->getTaxonomyId($record),
			'should_auto_renew'    => true,
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

		$this->listingService->updateListing($listingId, $data);

		$this->itemHelper->generateSku($listingId, $variationId);
	}
}
