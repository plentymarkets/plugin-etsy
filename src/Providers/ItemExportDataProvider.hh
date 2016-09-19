<?hh //strict

namespace Etsy\Providers;

use Plenty\Plugin\ConfigRepository;
use Etsy\Contracts\ItemDataProviderContract;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;

class ItemExportDataProvider implements ItemDataProviderContract
{
    /**
     * ItemDataLayerRepositoryContract $itemDataLayerRepository
     */
    private ItemDataLayerRepositoryContract $itemDataLayerRepository;

    /**
     * ConfigRepository $config
     */
    private ConfigRepository $config;

    /**
     * ItemDataLayerRepositoryContract $itemDataLayerRepository
     */
    public function __construct(
        ItemDataLayerRepositoryContract $itemDataLayerRepository,
        ConfigRepository $config
    )
    {
        $this->itemDataLayerRepository = $itemDataLayerRepository;
        $this->config  = $config;
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
                'id',
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
					'all_images' => [
						'type' => 'all', // all images
						'fileType' => ['gif', 'jpeg', 'jpg', 'png'],
						'imageType' => ['internal'],
						'referenceMarketplace' => $this->config->get('EtsyIntegrationPlugin.referrerId'),
					],
					'only_current_variation_images_and_generic_images' => [
						'type' => 'item_variation', // current variation + item images
                        'fileType' => ['gif', 'jpeg', 'jpg', 'png'],
						'imageType' => ['internal'],
						'referenceMarketplace' => $this->config->get('EtsyIntegrationPlugin.referrerId'),
					],
					'only_current_variation_images' => [
						'type' => 'variation', // current variation images
                        'fileType' => ['gif', 'jpeg', 'jpg', 'png'],
						'imageType' => ['internal'],
						'referenceMarketplace' => $this->config->get('EtsyIntegrationPlugin.referrerId'),
					],
					'only_generic_images' => [
						'type' => 'item', // only item images
                        'fileType' => ['gif', 'jpeg', 'jpg', 'png'],
						'imageType' => ['internal'],
						'referenceMarketplace' => $this->config->get('EtsyIntegrationPlugin.referrerId'),
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
