<?hh //strict

namespace Etsy\Providers;

use Etsy\Contracts\ItemDataProviderContract;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;

class ItemUpdateDataProvider implements ItemDataProviderContract
{
    const int LAST_UPDATE = 86400; // items updated in the last n seconds

    /**
     * ItemDataLayerRepositoryContract $itemDataLayerRepository
     */
    private ItemDataLayerRepositoryContract $itemDataLayerRepository;

    /**
     * @param ItemDataLayerRepositoryContract $itemDataLayerRepository
     */
    public function __construct(ItemDataLayerRepositoryContract $itemDataLayerRepository)
    {
        $this->itemDataLayerRepository = $itemDataLayerRepository;        
    }

    /**    
     * Fetch data using data layer. 
     * 
     * @return RecordList
     */
    public function fetch():RecordList
    {
        return $this->itemDataLayerRepository->search($this->resultFields(), $this->filters(), $this->params());
    }

    /**
     * Get the result fields needed. 
     * 
     * @return array<string, mixed>
     */
    private function resultFields():array<string, mixed>
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
     * Get the filters based on which we neeed to grab results.
     * 
     * @return array<string, mixed>
     */
    private function filters():array<string, mixed>
    {
        return [
            'variationBase.isActive?' => [],
            'variationVisibility.isVisibleForMarketplace' => [
                'mandatoryOneMarketplace' => [],
                'mandatoryAllMarketplace' => [
                    148 // TODO grab this from config.json
                ]
            ],
            'variationStock.wasUpdatedBetween' => [
                'timestampFrom' => (time() - self::LAST_UPDATE),    
                'timestampTo'   => time(),
            ],
            'variationMarketStatus.wasLastExportedBetween' =>[
                'timestampFrom' => (time() - self::LAST_UPDATE),
                'timestampTo' => time(),
                'marketplace' => 148, // TODO grab this from config.json
            ]
        ];
    }

    /**
     * Other parameters needed by the data layer to grab results.
     * 
     * @return array<string, mixed>
     */
    private function params():array<string, mixed>
    {
        return [
            'group_by' => 'groupBy.itemId'
        ];
    }
}