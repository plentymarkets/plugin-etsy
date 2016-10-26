<?php

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Order\OrderImportService;

/**
 * Class OrderImportCron
 */
class OrderImportCron extends Cron
{
	/**
	 * @param OrderImportService $service
	 */
	public function handle(OrderImportService $service)
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
	 * @return string
	 */
	private function lastRun()
	{
		return '2016-09-14 00:00:00';
	}

	/**
	 * Save the last run.
	 */
	private function saveLastRun()
	{
		// TODO save last run
	}
}
