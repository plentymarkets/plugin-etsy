<?hh //strict

namespace Etsy\Batch\Item;

use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Batch\AbstractBatchService as Service;
use Etsy\Contracts\ItemDataProviderContract;
use Etsy\Factories\ItemDataProviderFactory;

class ItemUpdateService extends Service
{
	public function __construct(ItemDataProviderFactory $itemDataProviderFactory)
	{
		parent::__construct($itemDataProviderFactory->make('update'));
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

        }
    }

}
