<?hh //strict

namespace Etsy\Batch;

use Plenty\Modules\Item\DataLayer\Models\RecordList;

use Etsy\Contracts\ItemDataProviderContract;

abstract class AbstractBatchService
{
    /**
     * ItemDataProvider $itemDataProvider;
     */
    private ItemDataProviderContract $itemDataProvider;

    /**
     * @param Client $client
     * @param ItemDataProviderContract $itemDataProvider
     */
    public function __construct(ItemDataProviderContract $itemDataProvider)
    {
        $this->itemDataProvider = $itemDataProvider;
    }

    final public function run():void
    {
        $result = $this->itemDataProvider->fetch();

        $this->export($result);
    }

    protected abstract function export(RecordList $recordList):void;
}
