<?php

namespace Etsy\DataProviders;

use Plenty\Plugin\Application;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;

use Etsy\Helper\OrderHelper;
use Etsy\Helper\SettingsHelper;
use Etsy\Contracts\ItemDataProviderContract;


/**
 * Class ItemExportDataProvider
 */
class ItemExportDataProvider implements ItemDataProviderContract
{
	/**
	 * @var ItemDataLayerRepositoryContract
	 */
	private $itemDataLayerRepository;

	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @var OrderHelper
	 */
	private $orderHelper;

	/**
	 * @var SettingsHelper
	 */
	private $settingsHelper;

	/**
	 * @param ItemDataLayerRepositoryContract $itemDataLayerRepository
	 * @param ConfigRepository                $config
	 * @param OrderHelper                     $orderHelper
	 * @param SettingsHelper                  $settingsHelper
	 */
	public function __construct(ItemDataLayerRepositoryContract $itemDataLayerRepository, ConfigRepository $config, OrderHelper $orderHelper, SettingsHelper $settingsHelper)
	{
		$this->itemDataLayerRepository = $itemDataLayerRepository;
		$this->config                  = $config;
		$this->orderHelper             = $orderHelper;
		$this->settingsHelper          = $settingsHelper;
	}

	/**
	 * Fetch data using data layer.
	 * @return RecordList
	 */
	public function fetch()
	{
		return $this->itemDataLayerRepository->search($this->resultFields(), $this->filters(), $this->params());
	}

	/**
	 * Get the result fields needed.
	 * @return array
	 */
	private function resultFields():array
	{
		$resultFields = [
			'itemBase' => [
				'id',
				'producer',
			],

			'itemShippingProfilesList' => [
				'id',
				'name',
			],

			'itemDescription' => [
				'params' => $this->getItemDescriptionParams(),
				'fields' => [
					'name1',
					'description',
					'shortDescription',
					'technicalData',
					'keywords',
					'lang',
				],
			],

			'variationMarketStatus' => [
				'params' => [
					'marketId' => $this->orderHelper->getReferrerId()
				],
				'fields' => [
					'sku'
				]
			],

			'variationBase' => [
				'id',
				'limitOrderByStockSelect',
			],

			'variationRetailPrice' => [
				'price',
			],

			'variationStock' => [
				'params' => [
					'type' => 'virtual'
				],
				'fields' => [
					'stockNet'
				]
			],

			'variationStandardCategory' => [
				'params' => [
					'plentyId' => pluginApp(Application::class)->getPlentyId(),
				],
				'fields' => [
					'categoryId'
				],
			],

			'itemCharacterList' => [
				'itemCharacterId',
				'characterId',
				'characterValue',
				'characterValueType',
				'isOrderCharacter',
				'characterOrderMarkup'
			],

			'variationImageList' => [
				'params' => [
					'all_images'                                       => [
						'type'                 => 'all', // all images
						'fileType'             => ['gif', 'jpeg', 'jpg', 'png'],
						'imageType'            => ['internal'],
						'referenceMarketplace' => $this->orderHelper->getReferrerId(),
					],
					'only_current_variation_images_and_generic_images' => [
						'type'                 => 'item_variation', // current variation + item images
						'fileType'             => ['gif', 'jpeg', 'jpg', 'png'],
						'imageType'            => ['internal'],
						'referenceMarketplace' => $this->orderHelper->getReferrerId(),
					],
					'only_current_variation_images'                    => [
						'type'                 => 'variation', // current variation images
						'fileType'             => ['gif', 'jpeg', 'jpg', 'png'],
						'imageType'            => ['internal'],
						'referenceMarketplace' => $this->orderHelper->getReferrerId(),
					],
					'only_generic_images'                              => [
						'type'                 => 'item', // only item images
						'fileType'             => ['gif', 'jpeg', 'jpg', 'png'],
						'imageType'            => ['internal'],
						'referenceMarketplace' => $this->orderHelper->getReferrerId(),
					],
				],
				'fields' => [
					'imageId',
					'type',
					'fileType',
					'path',
					'position',
					'attributeValueId',
				],
			],
		];

		return $resultFields;
	}

	/**
	 * Get the filters based on which we neeed to grab results.
	 *
	 * @return array
	 */
	private function filters()
	{
		return [
			'variationBase.isActive?'                     => [],
			'variationVisibility.isVisibleForMarketplace' => [
				'mandatoryOneMarketplace' => [],
				'mandatoryAllMarketplace' => [
					$this->orderHelper->getReferrerId()
				]
			]
		];
	}

	/**
	 * Other parameters needed by the data layer to grab results.
	 *
	 * @return array
	 */
	private function params()
	{
		return [
		];
	}

	/**
	 * Get the item description params.
	 *
	 * @return array
	 */
	private function getItemDescriptionParams()
	{
		$exportLanguages = $this->settingsHelper->getShopSettings('exportLanguages', [$this->settingsHelper->getShopSettings('mainLanguage', 'de')]);

		$list = [
			$this->settingsHelper->getShopSettings('mainLanguage', 'de') => [
				'language' => $this->settingsHelper->getShopSettings('mainLanguage', 'de'),
			]
		];

		foreach($exportLanguages as $language)
		{
			$list[ $language ] = [
				'language' => $language,
			];
		}

		return $list;
	}
}
