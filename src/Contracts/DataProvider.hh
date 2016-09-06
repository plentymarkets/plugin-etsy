<?hh //strict

namespace Etsy\Contracts;
use Plenty\Modules\Item\DataLayer\Models\RecordList;

/**
 * Interface DataProvider
 * @package Etsy\Contracts
 */
interface DataProvider
{
    /**
     * @param string $exportType
     * @return RecordList|null
     */
    public function getData(string $exportType):?RecordList;
}