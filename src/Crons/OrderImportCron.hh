<?hh //strict

namespace Etsy\Handler;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Order\OrderImportService;

class OrderImportCron extends Cron
{
	public function handle(OrderImportService $service):void
	{
		$service->run();
	}
}