<?hh //strict

namespace Etsy\Service;

use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Etsy\Api\Client;
use Etsy\Helper\DataHelper;
use Etsy\Contracts\DataProvider;

class Update
{
    /**
     * DataProvider $dataProvider
     * VariationSkuRepositoryContract $variationSkuRepository
     */
    private VariationSkuRepositoryContract $variationSkuRepository;

    /**
     * Client $client
     */
    private Client $client;

    /**
     * DataHelper $dataHelper
     */
    private DataHelper $dataHelper;

    /**
     * DataProvider $dataProvider;
     */
    private DataProvider $dataProvider;

    /**
     * Export constructor.
     *
     * @param Client $client
     * @param ItemDataService $itemDataService
     * @param VariationSkuRepositoryContract $variationSkuRepository
     * @param DataHelper $dataHelper
     * @param DataProvider $dataProvider
     */
    public function __construct(Client $client,
                                VariationSkuRepositoryContract $variationSkuRepository,
                                DataHelper $dataHelper,
                                DataProvider $dataProvider)
    {
        $this->client = $client;
        $this->variationSkuRepository = $variationSkuRepository;
        $this->dataHelper = $dataHelper;
        $this->dataProvider = $dataProvider;
    }

    /**
     * @return void
     */
    public function run():void
    {
        $result = $this->dataProvider->getData('Update');
        $this->update($result);
    }

    /**
     * Update all items on etsy
     *
     * @param ?RecordList $result
     * @return void
     */
    public function update(?RecordList $result):void
    {
        if($result instanceof RecordList)
        {
            foreach($result as $item)
            {
                $itemData = array();
                $itemData[] = null;
                if($item instanceof Record)
                {
                    $sku = (string)$item->variationMarketStatus->sku;
                    if(strlen($sku) > 0)
                    {

                        $itemData = [
                            'listing_id'            => (int)$item->variationMarketStatus->sku,
                            'quantity'              => $this->dataHelper->getStock($item),
                            'title'                 => $item->itemDescription->name1,
                            'description'           => $item->itemDescription->description,
                            'price'                 => $item->variationRetailPrice->price,
                            'wholesale_price'       => '',
                            'materials'             => '',
                            'renew'                 => 'no',
                            'shipping_template_id'  => '',
                            'shop_section_id'       => '',
                            'state'                 => 'inactive',      //todo setzt das listing inactive, muss für das testing gemacht werden
                            'image_ids'             => '',
                            'is_customizable'       => '',
                            'item_weight'           => '',
                            'item_length'           => '',
                            'item_width'            => '',
                            'item_height'           => '',
                            'item_weight_unit'      => '',
                            'item_dimensional_unit' => '',
                            'non_taxable'           => '',
                            'category_id'           => '',
                            'taxonomy_id'           => '',
                            'tags'                  => $item->itemDescription->keywords,
                            'who_made'              => '',
                            'is_supply'             => '',
                            'when_made'             => '',
                            'recipient'             => '',
                            'occasion'              => '',
                            'style'                 => '',
                            'processing_min'        => '',
                            'processing_max'        => '',
                            'featured_rank'         => '',
                        ];

                        $response = $this->client->call('updateListing', ['listing_id' => (int)$item->variationMarketStatus->sku], $itemData);

                        $response = json_decode($response);
                    }
                }
            }
        }
    }
}