<?hh //strict

namespace Etsy\Handler;

use Etsy\Service\OrderImportService;
use Plenty\Modules\Cron\Contracts\CronHandler;


class OrderImportHandler extends CronHandler
{
	public function handle(OrderImportService $orderImportService):void
	{
		$orderImportService->run();
	}
}