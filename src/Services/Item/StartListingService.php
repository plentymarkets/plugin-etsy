<?php

namespace Etsy\Services\Item;

use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Helper\ImageHelper;
use Etsy\Helper\SettingsHelper;
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
	 * @param ItemHelper                $itemHelper
	 * @param ListingService            $listingService
	 * @param DeleteListingService      $deleteListingService
	 * @param ListingImageService       $listingImageService
	 * @param Logger                    $logger
	 * @param ListingTranslationService $listingTranslationService
	 * @param SettingsHelper            $settingsHelper
	 * @param ImageHelper               $imageHelper
	 */
	public function __construct(
		ItemHelper $itemHelper,
		ListingService $listingService,
		DeleteListingService $deleteListingService,
		ListingImageService $listingImageService,
		Logger $logger,
		ListingTranslationService $listingTranslationService,
		SettingsHelper $settingsHelper,
		ImageHelper $imageHelper)
	{
		$this->itemHelper                = $itemHelper;
		$this->logger                    = $logger;
		$this->listingTranslationService = $listingTranslationService;
		$this->listingService            = $listingService;
		$this->deleteListingService      = $deleteListingService;
		$this->listingImageService       = $listingImageService;
		$this->settingsHelper            = $settingsHelper;
		$this->imageHelper               = $imageHelper;
	}

	/**
	 * Start the listing
	 *
	 * @param Record $record
	 */
	public function start(Record $record)
	{
		$listingId = $this->createListing($record);

		if(!is_null($listingId))
		{
			try
			{
				$this->addPictures($record, $listingId);

				$this->addTranslations($record, $listingId);

				$this->publish($listingId, $record->variationBase->id);
			}
			catch(\Exception $ex)
			{
				$this->deleteListingService->delete($listingId);

				$this->logger->log('Could not start listing for variation ID ' . $record->variationBase->id . ': ' . $ex->getMessage());
			}
		}
		else
		{
			$this->logger->log('Could not start listing for variation ID ' . $record->variationBase->id);
		}
	}

	/**
	 * Create a listing base.
	 *
	 * @param Record $record
	 *
	 * @return int
	 */
	private function createListing(Record $record)
	{
		$language    = $this->settingsHelper->getShopSettings('mainLanguage', 'de');

		$title       = $this->itemHelper->getVariationWithAttributesName($record, $language);
		$description = strip_tags($record->itemDescription[ $language ]['description']);

		$data = [
			'state'                => 'draft',
			'title'                => $title,
			'description'          => $description,
			'quantity'             => $this->itemHelper->getStock($record),
			'price'                => number_format($record->variationRetailPrice->price, 2),
			'currency_code'        => $record->variationRetailPrice->currency,
			'shipping_template_id' => $this->itemHelper->getShippingTemplateId($record),
			'taxonomy_id'          => $this->itemHelper->getTaxonomyId($record),
			'should_auto_renew'    => 'true',
			'is_digital'           => 'false',
			'is_supply'            => 'false',

			// TODO
			// materials
			// shop_section_id
			// processing_min
			// processing_max

		];

		if($isSupply = $this->itemHelper->getProperty($record, 'is_supply', $language))
		{
			$data['is_supply'] = $isSupply;
		}

		if(strlen($record->itemDescription[ $language ]['keywords']))
		{
			$data['tags'] = $record->itemDescription[ $language ]['keywords'];
		}

		if($whoMade = $this->itemHelper->getProperty($record, 'who_made', 'en'))
		{
			$data['who_made'] = $whoMade;
		}

		if($whenMade = $this->itemHelper->getProperty($record, 'when_made', 'en'))
		{
			$data['when_made'] = $whenMade;
		}

		if($occasion = $this->itemHelper->getProperty($record, 'occasion', 'en'))
		{
			$data['occasion'] = $occasion;
		}

		if($recipient = $this->itemHelper->getProperty($record, 'recipient', 'en'))
		{
			$data['recipient'] = $recipient;
		}

		if($itemWeight = $record->variationBase->weightG)
		{
			$data['item_weight']       = $itemWeight;
			$data['item_weight_units'] = 'g';
		}

		if($itemHeight = $record->variationBase->heightMm)
		{
			$data['item_height']          = $itemHeight;
			$data['item_dimensions_unit'] = 'mm';
		}

		if($itemLength = $record->variationBase->lengthMm)
		{
			$data['item_length']          = $itemLength;
			$data['item_dimensions_unit'] = 'mm';
		}

		if($itemWidth = $record->variationBase->widthMm)
		{
			$data['item_width']           = $itemWidth;
			$data['item_dimensions_unit'] = 'mm';
		}

		return $this->listingService->createListing($this->settingsHelper->getShopSettings('shopLanguage', 'de'), $data); // TODO replace all languages with the shop language
	}

	/**
	 * Add pictures to listing.
	 *
	 * @param Record $record
	 * @param int    $listingId
	 */
	private function addPictures(Record $record, $listingId)
	{
		$list = $this->itemHelper->getImageList($record->variationImageList['all_images']->toArray(), 'normal');

		$imageList = [];

		$list = array_slice($list, 0, 5);

		foreach($list as $id => $image)
		{
			$response = $this->listingImageService->uploadListingImage($listingId, $image);

			if(isset($response['results']) && isset($response['results'][0]) && isset($response['results'][0]['listing_image_id']))
			{
				$imageList[] = [
					'imageId'        => $id,
					'listingImageId' => $response['results'][0]['listing_image_id'],
					'listingId'      => $response['results'][0]['listing_id'],
					'imageUrl'       => $image,
				];

			}
		}

		if(count($imageList))
		{
			$this->imageHelper->save($record->variationBase->id, json_encode($imageList));
		}
	}

	/**
	 * Add translations to listing.
	 *
	 * @param Record $record
	 * @param int    $listingId
	 */
	private function addTranslations(Record $record, $listingId)
	{
		foreach($this->settingsHelper->getShopSettings('exportLanguages', [$this->settingsHelper->getShopSettings('mainLanguage', 'de')]) as $language)
		{
			if($language != $this->settingsHelper->getShopSettings('mainLanguage', 'de') && $record->itemDescription[ $language ]['name1'] && strip_tags($record->itemDescription[ $language ]['description']))
			{
				try
				{
					$data = [
						'title'       => $record->itemDescription[ $language ]['name1'],
						'description' => strip_tags($record->itemDescription[ $language ]['description']),
					];

					if($record->itemDescription[ $language ]['keywords'])
					{
						$data['tags'] = $record->itemDescription[ $language ]['keywords'];
					}

					$this->listingTranslationService->createListingTranslation($listingId, $language, $data);
				}
				catch(\Exception $ex)
				{
					$this->logger->log('Could not upload translation for listing ID ' . $listingId . ': ' . $ex->getMessage());
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
