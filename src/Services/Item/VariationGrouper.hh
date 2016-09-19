<?hh //strict
namespace Etsy\Services\Item;

use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Models\Record;

class VariationGrouper
{
	/**
	 * @var RecordList $recordList
	 */
	private RecordList $recordList;

	/**
	 * @param RecordList $recordList
	 */
	public function __construct(RecordList $recordList)
	{
		$this->recordList = $recordList;
	}

	/**
	 * Check whether $list contains more entries.
     *
	 * @return bool
	 */
	public function hasNext():bool
	{
		if ($this->recordList instanceof RecordList &&
			$this->recordList->count() > 0 &&
			$this->recordList->valid())
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns a group of variations with same itemId
	 * @return array
	 */
	public function nextGroup():array<int,Record>
	{
		$currentItemList = [];

		$currentItemId = null;

		while($this->hasNext())
		{
			$variation = $this->recordList->current();
			if(!($variation instanceof Record))
			{
				break;
			}
			$this->recordList->next();

			if (is_null($currentItemId))
			{
				$currentItemId = $variation->itemBase->id;
			}

			if ($variation->itemBase->id == $currentItemId)
			{
				$currentItemList[] = $variation;
			}
			else
			{
				break;
			}
		}

		return $currentItemList;
	}
}
