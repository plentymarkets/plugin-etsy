<?hh //strict

namespace Etsy\Service\DataProvider;

use Etsy\Helper\DataHelper;
use Etsy\Contracts\DataProvider;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Item\DataLayer\Models\RecordList;

class ItemDataProvider implements DataProvider
{
    /**
     * DataHelper $dataHelper
     */
    private DataHelper $dataHelper;

    /**
     * ItemDataLayerRepositoryContract $itemDataLayer
     */
    private ItemDataLayerRepositoryContract $itemDataLayer;

    /**
     * RecordList $recordList
     */
    private RecordList $recordList;

    /**
     * Data constructor.
     *
     * @param DataHelper $dataHelper
     * @param ItemDataLayerRepositoryContract $itemDataLayer
     * @param RecordList $recordList
     */
    public function __construct(DataHelper $dataHelper, ItemDataLayerRepositoryContract $itemDataLayer, RecordList $recordList)
    {
        $this->dataHelper = $dataHelper;
        $this->itemDataLayer = $itemDataLayer;
        $this->recordList = $recordList;
    }

    /**
     * @param string $exportType
     * @return RecordList|null
     */
    public function getData(string $exportType):?RecordList
    {
        if($exportType == 'Export')
        {
            $params = array();
            $resultFields = $this->dataHelper->getItemData();
            $filter = $this->dataHelper->getExportFilter();
            $params['groupe_by'] = 'groupBy.itemId';
            $result = $this->itemDataLayer->search($resultFields, $filter, $params);
            return $result;
        }
        elseif($exportType == 'Update')
        {
            $params = array();
            $resultFields = $this->dataHelper->getItemData();
            $filter = $this->dataHelper->getUpdateFilter();
            $params['groupe_by'] = 'groupBy.itemId';
            $result = $this->itemDataLayer->search($resultFields, $filter, $params);
            return $result;
        }
        else
        {
            return null;
        }
    }
}