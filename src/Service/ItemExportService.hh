<?hh //strict

namespace Etsy\Service;

use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Etsy\Api\Client;
use Etsy\Helper\DataHelper;
use Etsy\Contracts\DataProvider;

class Export
{
    /**
     * DataProvider $dataProvider
    th
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
        $result = $this->dataProvider->getData('Export');
        $this->export($result);
    }

    /**
     * Export all article to etsy which are not exported yet
     *
     * @param ?RecordList $result
     * @return void
     */
    public function export(?RecordList $result):void
    {
        if($result instanceof RecordList)
        {
            foreach($result as $item)
            {
                $itemData = array();
                $itemData[] = null;

                if($item instanceof Record)
                {
                    if(strlen($item->variationMarketStatus->sku) > 0)
                    {
                        continue;
                    }

                    $itemData = [
                        'quantity'              => $this->dataHelper->getStock($item),
                        'title'                 => $item->itemDescription->name1,
                        'description'           => $item->itemDescription->description,
                        'price'                 => $item->variationRetailPrice->price,
                        'materials'             => '',
                        'shipping_template_id'  => '',
                        'shop_section_id'       => '',
                        'image_ids'             => '',
                        'is_customizable'       => '',
                        'non_taxable'           => '',
                        'image'                 => '',
                        'state'                 => 'edit',      //todo edit setzt das listing inactive, muss für das testing gemacht werden
                        'processing_min'        => '',
                        'processing_max'        => '',
                        'category_id'           => '',
                        'taxonomy_id'           => '',
                        'tags'                  => $item->itemDescription->keywords,
                        'who_made'              => 'i_did',
                        'is_supply'             => 'false',
                        'when_made'             => 'made_to_order',
                        'recipient'             => '',
                        'occasion'              => '',
                        'style'                 => '',
                    ];

                    $response = $this->client->call('createListing', [], $itemData);

                    $response = json_decode($response);
                    $listingId = $response->result[0]->listing_id;

                    if($listingId > 0 && !$listingId == null)
                    {
                        $this->variationSkuRepository->generateSku($item->variationBase->id, 148, 0, (string)$listingId); //todo 148 (web API) ist die test marketnumber
                    }
                    else
                    {
                    }
                }
            }
        }
    }
}