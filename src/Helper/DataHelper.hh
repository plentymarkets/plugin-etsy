<?hh //strict

namespace Etsy\Helper;

use Plenty\Modules\Item\DataLayer\Models\Record;

class DataHelper
{
    /**
     * @param Record $item
     *
     * @return int
     */
    public function getStock(Record $item):int
    {
        if($item->variationBase->limitOrderByStockSelect == 2)
        {
            $stock = 999;
        }
        elseif($item->variationBase->limitOrderByStockSelect == 1 && $item->variationStock->stockNet > 0)
        {
            if($item->variationStock->stockNet > 999)
            {
                $stock = 999;
            }
            else
            {
                $stock = $item->variationStock->stockNet;
            }
        }
        elseif($item->variationBase->limitOrderByStockSelect == 0)
        {
            if($item->variationStock->stockNet > 999)
            {
                $stock = 999;
            }
            else
            {
                if($item->variationStock->stockNet > 0)
                {
                    $stock = $item->variationStock->stockNet;
                }
                else
                {
                    $stock = 0;
                }
            }
        }
        else
        {
            $stock = 0;
        }

        return $stock;
    }

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

    /**
     * Filter for all article which are available for the item export
     *
     * @return array
     */
    public function getExportFilter():array<string, mixed>
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
}