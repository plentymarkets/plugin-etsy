<?hh //strict

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Item\ItemExportService;

class ItemExportCron extends Cron
{
    public function handle(ItemExportService $service):void
    {
        $service->run();
    }
}