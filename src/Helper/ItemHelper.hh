<?hh //strict

namespace Etsy\Helper;

use Plenty\Modules\Item\DataLayer\Models\Record;

class ItemHelper
{
    /**
     * Get the stock based on the settings.
     * 
     * @param Record $item
     * @return int
     */
    public function getStock(Record $item):int
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

        return $stock;
    }
}