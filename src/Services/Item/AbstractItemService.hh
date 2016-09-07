<?hh //strict

namespace Etsy\Services\Item;

use Plenty\Modules\Item\DataLayer\Models\RecordList;

use Etsy\Api\Client;
use Etsy\Contracts\ItemDataProviderContract;

abstract class AbstractItemService
{	
    /**
     * Client $client
     */
    protected Client $client;

    /**
     * ItemDataProvider $itemDataProvider;
     */
    private ItemDataProviderContract $itemDataProvider;

    /**
     * @param Client $client         
     * @param ItemDataProviderContract $itemDataProvider
     */
    public function __construct(
        Client $client,                
        ItemDataProviderContract $itemDataProvider
    )
    {
        $this->client = $client;        
        $this->itemDataProvider = $itemDataProvider;
    }
    
    final public function run():void
    {
        $result = $this->itemDataProvider->fetch();

        $this->export($result);
    }

    protected abstract function export(RecordList $recordList):void;
}