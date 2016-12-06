<?php

namespace Etsy\DataProviders;

use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Plugin\ConfigRepository;

use Etsy\Contracts\ItemDataProviderContract;
use Etsy\Helper\OrderHelper;


/**
 * Class ItemUpdateDataProvider
 */
class ItemUpdateDataProvider implements ItemDataProviderContract
{
	const LAST_UPDATE = 86400; // items updated in the last n seconds

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
	 *
	 * @return RecordList
	 */
	public function fetch()
	{
		return $this->itemDataLayerRepository->search($this->resultFields(), $this->filters());
	}

	/**
	 * Get the result fields needed.
	 *
	 * @return array
	 */
	private function resultFields()
	{
		$resultFields = [
			'itemBase' => [
				'id',
				'producer',
			],

			'variationRetailPrice' => [
				'price',
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
				'active'
			],

			'variationStock' => [
				'params' => [
					'type' => 'virtual'
				],
				'fields' => [
					'stockNet'
				]
			],

			'variationLinkMarketplace' => [
				'marketplaceId'
			],
		];

		return $resultFields;
	}

	/**
	 * Get the filters based on which we need to grab results.
	 *
	 * @return array
	 */
	private function filters()
	{
		return [
			'variationStock.wasUpdatedBetween'       => [
				'timestampFrom' => (time() - self::LAST_UPDATE),
				'timestampTo'   => time(),
			],
			'variationMarketStatus.hasMarketStatus?' => [
				'marketplace' => $this->orderHelper->getReferrerId()
			]
		];
	}
}
