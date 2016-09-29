<?hh //strict

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Batch\Item\ItemStockUpdateService;

class ItemUpdateCron extends Cron
{
    public function handle(ItemStockUpdateService $service):void
    {
        $service->run();
    }
}
