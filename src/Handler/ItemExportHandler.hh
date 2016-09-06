<?hh //strict

namespace Etsy\Handler;

use Etsy\Service\Export;
use Etsy\Service\Update;
use Plenty\Modules\Cron\Contracts\CronHandler;


class ItemExportHandler extends CronHandler
{
    public function handle(Export $export):void
    {
        $export->run();
    }
}