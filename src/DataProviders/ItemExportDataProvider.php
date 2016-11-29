<?php

namespace Etsy\DataProviders;

use Etsy\Helper\OrderHelper;
use Plenty\Plugin\ConfigRepository;
use Etsy\Contracts\ItemDataProviderContract;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;

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
	 * @param ItemDataLayerRepositoryContract $itemDataLayerRepository
	 * @param ConfigRepository                $config
	 * @param OrderHelper                     $orderHelper
	 */
	public function __construct(ItemDataLayerRepositoryContract $itemDataLayerRepository, ConfigRepository $config, OrderHelper $orderHelper)
	{
		$this->itemDataLayerRepository = $itemDataLayerRepository;
		$this->config                  = $config;
		$this->orderHelper             = $orderHelper;
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

			'itemDescriptionList' => [
				'name1',
				'description',
				'shortDescription',
				'technicalData',
				'keywords',
				'lang'
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
			'group_by' => 'groupBy.itemId'
		];
	}
}
