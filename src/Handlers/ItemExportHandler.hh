<?hh //strict

namespace Etsy\Handlers;

use Etsy\Services\ItemExportService;
use Plenty\Modules\Cron\Contracts\CronHandler;


class ItemExportHandler extends CronHandler
{
    public function handle(ItemExportService $export):void
    {
        $export->run();
    }
}