<?php
namespace Etsy\Services\Item;

use Etsy\Api\Services\ListingTranslationService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Etsy\Api\Services\ListingService;
use Etsy\Helper\ItemHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Plugin\Log\Loggable;

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
	 * @param ItemHelper                 $itemHelper
	 * @param ConfigRepository           $config
	 * @param ListingService             $listingService
	 * @param SettingsHelper             $settingsHelper
	 * @param ListingTranslationService  $listingTranslationService
	 */
	public function __construct(ItemHelper $itemHelper, ConfigRepository $config, ListingService $listingService, SettingsHelper $settingsHelper, ListingTranslationService $listingTranslationService)
	{
		$this->config                    = $config;
		$this->settingsHelper            = $settingsHelper;
		$this->itemHelper                = $itemHelper;
		$this->listingService            = $listingService;
		$this->listingTranslationService = $listingTranslationService;
	}

	/**
	 * @param Record $record
	 */
	public function update(Record $record)
	{
		$listingId = $record->variationMarketStatus->sku;

		if(!is_null($listingId))
		{
			try
			{
				$this->addTranslations($record, $listingId);
				
				$this->updateListing($record, $listingId);

				// TODO: Pictures in later sprints
//				$this->addPictures($record, $listingId);
			}
			catch(\Exception $ex)
			{
				$this->getLogger(__FUNCTION__)
					->setReferenceType('variationId')
					->setReferenceValue($record->variationBase->id)
					->error('Etsy::item.updateListingError', $ex->getMessage());
			}
		}
		else
		{
			$this->getLogger(__FUNCTION__)
				->setReferenceType('variationId')
				->setReferenceValue($record->variationBase->id)
				->info('Etsy::item.updateListingError');
		}
	}

	/**
	 * @param Record $record
	 * @param int    $listingId
	 */
	private function updateListing(Record $record, $listingId)
	{
		$language    = $this->settingsHelper->getShopSettings('mainLanguage', 'de');

		$title       = $this->itemHelper->getVariationWithAttributesName($record, $language);
		$description = html_entity_decode(strip_tags($record->itemDescription[ $language ]['description']));

		$data = [
			'listing_id'           => (int) $listingId,
			'title'                => $title,
			'description'          => $description,
			'shipping_template_id' => $this->itemHelper->getShippingTemplateId($record),
			'taxonomy_id'          => $this->itemHelper->getTaxonomyId($record),
			// TODO: Pictures with dynamodb
		];

		if($isSupply = $this->itemHelper->getProperty($record, 'is_supply', $language))
		{
			$data['is_supply'] = $isSupply;
		}

		if(strlen($record->itemDescription[ $language ]['keywords']))
		{
			$data['tags'] = $this->itemHelper->getTags($record, $language);
		}

		if($whoMade = $this->itemHelper->getProperty($record, 'who_made', 'en'))
		{
			$data['who_made'] = $whoMade;
		}

		if($whenMade = $this->itemHelper->getProperty($record, 'when_made', 'en'))
		{
			$data['when_made'] = $whenMade;
		}

		if($occasion = $this->itemHelper->getProperty($record, 'occasion', $language))
		{
			$data['occasion'] = $occasion;
		}

		if($recipient = $this->itemHelper->getProperty($record, 'recipient', $language))
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

		$this->listingService->updateListing($listingId, $data, $language);
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
						'description' => html_entity_decode(strip_tags($record->itemDescription[ $language ]['description'])),
					];

					if($record->itemDescription[ $language ]['keywords'])
					{
						$data['tags'] = $this->itemHelper->getTags($record, $language);
					}

					$this->listingTranslationService->updateListingTranslation($listingId, $language, $data);
				}
				catch(\Exception $ex)
				{
					$this->getLogger(__FUNCTION__)
						->setReferenceType('listingId')
						->setReferenceValue($listingId)
						->error('Etsy::item.translationUpdateError', $ex->getMessage());
				}
			}
		}
	}
}
