<?hh //strict

namespace Etsy\Service;

use Etsy\Service\ItemDataService;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use ElasticExport\Helper\ElasticExportHelper;

class Export
{

    const API_KEY       = 'pb3qb621tm9boiau3nm7m25l';
    const URL			= 'https://openapi.etsy.com/v2/';

    /**
     * ItemDataService $itemDataService
     */
    private ItemDataService $itemDataService;

    /**
     * ElasticExportHelper $elasticExportHelper
     */
    private ElasticExportHelper $elasticExportHelper;

    /**
     * ItemDataLayerRepositoryContract $itemDataLayer
     */
    private ItemDataLayerRepositoryContract $itemDataLayer;

    /**
     * VariationSkuRepositoryContract $variationSkuRepository
     */
    private VariationSkuRepositoryContract $variationSkuRepository;

    /**
     * Export constructor.
     *
     * @param ItemDataLayerRepositoryContract $itemDataLayer
     * @param ItemDataService $itemDataService
     * @param ElasticExportHelper $elasticExportHelper
     * @param VariationSkuRepositoryContract $variationSkuRepository
     */
    public function __construct(ItemDataLayerRepositoryContract $itemDataLayer,
                                ItemDataService $itemDataService,
                                ElasticExportHelper $elasticExportHelper,
                                VariationSkuRepositoryContract $variationSkuRepository)
    {
        $this->itemDataLayer = $itemDataLayer;
        $this->itemDataService = $itemDataService;
        $this->elasticExportHelper = $elasticExportHelper;
        $this->variationSkuRepository = $variationSkuRepository;
    }

    /**
     * Export all article to etsy which are not exported yet
     *
     * @return void
     */
    public function export():void
    {
        $params = array();
        $resultFields = $this->itemDataService->getItemData();
        $filter = $this->itemDataService->getFilter();
        $params['groupe_by'] = 'groupBy.itemId';

        $result = $this->itemDataLayer->search($resultFields, $filter, $params);

        if($result instanceof RecordList)
        {
            foreach($result as $item)
            {
                $itemData = array();
                $itemData[] = null;

                if(strlen($item->variationMarketStatus->sku) > 0)
                {
                    continue;
                }
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
                    'tags'                  => $item->itemDescription->keywords,
                    'who_made'              => 'i_did',
                    'is_supply'             => 'false',
                    'when_made'             => 'made_to_order',
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
                    $this->variationSkuRepository->generateSku($item->variationBase->id, 148, 0, (string)$listingId); //todo 148 (web API) ist die test marketnumber
                }
                else
                {
                }
            }
        }
    }

    /**
     * Update all article which are already successful exported and from which the stock changed.
     *
     * return void
     */
    public function update():void
    {
        $params = array();
        $resultFields = $this->itemDataService->getItemData();
        $filter = $this->itemDataService->getUpdateFilter();
        $params['groupe_by'] = 'groupBy.itemId';

        $result = $this->itemDataLayer->search($resultFields, $filter, $params);

        if($result instanceof RecordList)
        {
            foreach($result as $item)
            {
                $itemData = array();
                $itemData[] = null;
                $sku = (string)$item->variationMarketStatus->sku;
                if(strlen($sku) > 0)
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

                    $itemData = [
                        'listing_id'            => (int)$item->variationMarketStatus->sku,
                        'quantity'              => $stock,
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

                    $request = json_decode($itemData);

                    $url = self::URL.'listings/'.$sku.'?api_key='.self::API_KEY;

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

                    $response = curl_exec($ch);

                    curl_close($ch);

                    $response = json_decode($response);
                }
            }
        }
    }
}