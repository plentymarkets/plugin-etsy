<?hh //strict

namespace Etsy\Adapter;

use Etsy\Feed\Feed;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use ElasticExport\Helper\ElasticExportHelper;

class Export
{

    const API_KEY       = 'pb3qb621tm9boiau3nm7m25l';
    const URL			= 'https://openapi.etsy.com/v2/';

    /**
     * Feed $feed
     */
    private Feed $feed;

    /**
     * ElasticExportHelper $elasticExportHelper
     */
    private ElasticExportHelper $elasticExportHelper;

    /**
     * ItemDataLayerRepositoryContract $itemDataLayer
     */
    private ItemDataLayerRepositoryContract $itemDataLayer;

    /**
     * Export constructor.
     *
     * @param Feed $feed
     * @param ItemDataLayerRepositoryContract $itemDataLayer
     * @param ElasticExportHelper $elasticExportHelper
     */
    public function __construct(ItemDataLayerRepositoryContract $itemDataLayer,
                                Feed $feed,
                                ElasticExportHelper $elasticExportHelper)
    {
        $this->itemDataLayer = $itemDataLayer;
        $this->feed = $feed;
        $this->elasticExportHelper = $elasticExportHelper;
    }

    /**
     * return void
     */
    public function CatalogExport():void
    {
        $params = array();
        $resultFields = $this->feed->getItemFeed();
        $filter = $this->feed->getFilter();
        $params['groupe_by'] = 'groupBy.itemIdGetPrimaryVariation';

        $result = $this->itemDataLayer->search($resultFields, $filter, $params);

        if($result instanceof RecordList)
        {
            foreach($result as $item)
            {
                $itemData = array();
                $itemData[] = null;
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

                $itemData = [
                    'quantity'              => $stock,
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
                    'tags'                  => '',
                    'who_made'              => '',
                    'is_supply'             => '',
                    'when_made'             => '',
                    'recipient'             => '',
                    'occasion'              => '',
                    'style'                 => '',
                ];

                $request = json_decode($itemData);

                $url = self::URL.'listings?api_key='.self::API_KEY;

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

                $response = curl_exec($ch);

                curl_close($ch);

                $response = json_decode($response);
                $listingId = $response->result[0]->listing_id;

                if($listingId > 0 && !$listingId == null)
                {
                    $this->elasticExportHelper->generateSku($item, 3000, (string)$listingId); //todo 3000 ist die test marketnumber
                }
                else
                {
                }
            }
        }
    }

    /**
     * return void
     */
    public function CatalogUpdate():void
    {
    }
}