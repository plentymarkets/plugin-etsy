<?hh //strict

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Item\ItemUpdateService;

class ItemUpdateCron extends Cron
{
    public function handle(ItemUpdateService $service):void
    {
        $service->run();
    }
}