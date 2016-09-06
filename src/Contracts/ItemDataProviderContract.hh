<?hh //strict

namespace Etsy\Contracts;
use Plenty\Modules\Item\DataLayer\Models\RecordList;

/**
 * Interface ItemDataProvider
 * @package Etsy\Contracts
 */
interface ItemDataProviderContract
{
    /**     
     * @return RecordList
     */
    public function fetch():RecordList;
}