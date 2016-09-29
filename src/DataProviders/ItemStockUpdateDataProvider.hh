<?hh //strict

namespace Etsy\DataProviders;

use Plenty\Plugin\ConfigRepository;
use Etsy\Contracts\ItemDataProviderContract;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;

class ItemStockUpdateDataProvider implements ItemDataProviderContract
{
    const int LAST_UPDATE = 86400; // items updated in the last n seconds

    /**
     * ItemDataLayerRepositoryContract $itemDataLayerRepository
     */
    private ItemDataLayerRepositoryContract $itemDataLayerRepository;

    /**
     * ConfigRepository $config
     */
    private ConfigRepository $config;

    /**
     * ItemUpdateDataProvider constructor.
     * @param ItemDataLayerRepositoryContract $itemDataLayerRepository
     * @param ConfigRepository $config
     */
    public function __construct(ItemDataLayerRepositoryContract $itemDataLayerRepository,
                                ConfigRepository $config)
    {
        $this->itemDataLayerRepository = $itemDataLayerRepository;
        $this->config = $config;
    }

    /**
     * Fetch data using data layer.
     *
     * @return RecordList
     */
    public function fetch():RecordList
    {
        return $this->itemDataLayerRepository->search($this->resultFields(), $this->filters());
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
                'id',
                'producer',
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
                'id',
                'limitOrderByStockSelect',
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
     * Get the filters based on which we need to grab results.
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
            'variationMarketStatus.hasMarketStatus?' => [
                'marketplace' => 148 // TODO grab this from config.json
            ]
        ];
    }
}
