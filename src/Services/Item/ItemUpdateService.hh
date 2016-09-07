<?hh //strict

namespace Etsy\Services\Item;

use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Services\Item\AbstractItemService as Service;
use Etsy\Helper\ItemHelper;
use Etsy\Api\Client;
use Etsy\Contracts\ItemDataProviderContract;
use Etsy\Factories\ItemDataProviderFactory;

class ItemUpdateService extends Service
{	
	/**
	 * VariationSkuRepositoryContract $variationSkuRepository
	 */
	private VariationSkuRepositoryContract $variationSkuRepository;

	/**
	 * ItemHelper $itemHelper
	 */
	private ItemHelper $itemHelper;

	public function __construct(
		Client $client, 
		ItemDataProviderFactory $itemDataProviderFactory,
		VariationSkuRepositoryContract $variationSkuRepository,
		ItemHelper $itemHelper
	)
	{        
        $this->variationSkuRepository = $variationSkuRepository;
        $this->itemHelper = $itemHelper;

		parent::__construct($client, $itemDataProviderFactory->make('update'));
	}

    /**
     * Update all article which are not updated yet.
     *
     * @param RecordList $records
     * @return void
     */
    protected function export(RecordList $records):void
    {        
        foreach($records as $item)
        {
        	$this->startListing($item);
        }        
    }

	/**
	 * Start a listing.
	 * @param Record $item
	 * @return void
	 */
    private function startListing(Record $item):void
    {
    	if(!$this->valid($item))
    	{
    		return;
    	}

        $itemData = [
            'quantity'              => 10,
            'title'                 => 'TEST ITEM',
            'description'           => 'TEST ITEM PLEASE DO NOT BUY',
            'price'                 => 0.20,
            'shipping_template_id'  => 28706227157,
            'state'                 => 'draft',
            'who_made'              => 'i_did',
            'is_supply'             => 'false',
            'when_made'             => 'made_to_order'
        ];
    }

    /**
     * Check if the item is valid for update.
     * 
     * @param Record $item
     * @return bool
     */
    private function valid(Record $item):bool
    {
		if(strlen($item->variationMarketStatus->sku) > 0)
        {
            return false;
        }

        return true;
    }
}