<?hh //strict

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Order\OrderImportService;

class OrderImportCron extends Cron
{
	public function handle(OrderImportService $service):void
	{
		try
		{
			$service->run($this->lastRun(), date('c'));

			$this->saveLastRun();
		}
		catch(\Exception $ex)
		{
			// TODO Log exception
		}
	}

	/**
	 * Get the last run.
	 *
	 * @return string
	 */
	private function lastRun():string
	{
		return '2016-09-14 00:00:00';
	}

	/**
	 * Save the last run.
	 */
	private function saveLastRun():void
	{
		// TODO save last run
	}
}
