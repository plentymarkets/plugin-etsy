<?hh //strict

namespace Etsy\Handler;

use Etsy\Service\OrderImport;
use Plenty\Modules\Cron\Contracts\CronHandler;


class OrderImportHandler extends CronHandler
{
	public function handle(OrderImport $orderImport):void
	{
		$orderImport->run();
	}
}