<?hh //strict

namespace Etsy\Feed;

class Feed
{
    /**
     * @return array
     */
    public function getItemFeed():array<string, mixed>
    {
        return [
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
    }

    /**
     * @return array
     */
    public function getFilter():array<string, mixed>
    {
        $searchFilter = [
            'variationBase.isActive?' => [],
            'variationVisibility.isVisibleForMarketplace' => [
                'mandatoryOneMarketplace' => [],
                'mandatoryAllMarketplace' => [
                    3000
                ]
            ]
        ];
        return $searchFilter;
    }
}