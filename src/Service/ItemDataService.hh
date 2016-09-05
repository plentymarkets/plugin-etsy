<?hh //strict

namespace Etsy\Service;

class ItemDataService
{
    /**
     * Returns all result fields which are necessary for a successful export
     *
     * @return array
     */
    public function getItemData():array<string, mixed>
    {
        $resultFields = [
            'itemBase' => [
                'producer',
            ],

            'itemDescription' => [
                'params' => [
                    'language' => 'de',
                ],
                'fields' => [
					'name1',
					'description',
					'shortDescription',
					'technicalData',
                    'keywords'
                ],
            ],

            'variationMarketStatus' => [
				'params' => [
					'marketId' => 148
				],
				'fields' => [
					'sku'
				]
			],

            'variationBase' => [
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
        ];
        return $resultFields;
    }

    /**
     * Filter for all article which are available for the item export
     *
     * @return array
     */
    public function getFilter():array<string, mixed>
    {
        $searchFilter = [
            'variationBase.isActive?' => [],
            'variationVisibility.isVisibleForMarketplace' => [
                'mandatoryOneMarketplace' => [],
                'mandatoryAllMarketplace' => [
                    148
                ]
            ]
        ];
        return $searchFilter;
    }

    /**
     * A Filter for article which are already exported to etsy and need to be updated
     *
     * @return array
     */
    public function getUpdateFilter():array<string, mixed>
    {
        $searchFilter = [
            'variationBase.isActive?' => [],
            'variationVisibility.isVisibleForMarketplace' => [
                'mandatoryOneMarketplace' => [],
                'mandatoryAllMarketplace' => [
                    148
                ]
            ],
            'variationStock.wasUpdatedBetween' => [
                'timestampFrom' => (time() - 86400),	// 1 day
                'timestampTo'	=> time(),
            ],
            'variationMarketStatus.wasLastExportedBetween' =>[
                'timestampFrom' => (time() - 86400),	// 1 day
                'timestampTo' => time(),
                'marketplace' => 148,
            ]
        ];
        return $searchFilter;

    }
}