<?php

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Helper\SettingsHelper;
use Etsy\Services\Order\OrderImportService;

/**
 * Class OrderImportCron
 */
class OrderImportCron extends Cron
{
	const MAX_DAYS = 3;

	private $settingsHelper;

	/**
	 * @param SettingsHelper $settingsHelper
	 */
	public function __construct(SettingsHelper $settingsHelper)
	{
		$this->settingsHelper = $settingsHelper;
	}

	/**
	 * Handle the cron execution.
	 *
	 * @param OrderImportService $service
	 */
	public function handle(OrderImportService $service)
	{
		try
		{
			$service->run($this->lastRun(), date('Y-m-d H:i:s'));

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
	private function lastRun()
	{
		$lastRun = $this->settingsHelper->get(SettingsHelper::SETTINGS_LAST_ORDER_IMPORT);

		if(!$lastRun || strlen($lastRun) <= 0)
		{
			return date('Y-m-d H:i:s', time() - (60 * 60 * 24 * self::MAX_DAYS));
		}

		return $lastRun;
	}

	/**
	 * Save the last run.
	 */
	private function saveLastRun()
	{
		$this->settingsHelper->save(SettingsHelper::SETTINGS_LAST_ORDER_IMPORT, date('Y-m-d H:i:s'));
	}
}