<?php

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Helper\SettingsHelper;
use Etsy\Helper\AccountHelper;
use Etsy\Services\Order\OrderImportService;
use Plenty\Plugin\Log\Loggable;

/**
 * Class OrderImportCron
 */
class OrderImportCron extends Cron
{
	use Loggable;

	const MAX_DAYS = 3;

	/**
	 * @var SettingsHelper
	 */
	private $settingsHelper;

	/**
	 * @param SettingsHelper $settingsHelper
	 */
	public function __construct(SettingsHelper $settingsHelper)
	{
		$this->settingsHelper = $settingsHelper;
	}

	/**
	 * Run the order import process.
	 *
	 * @param OrderImportService $service
	 * @param AccountHelper      $accountHelper
	 */
	public function handle(OrderImportService $service, AccountHelper $accountHelper)
	{
		try
		{
			if($accountHelper->isProcessActive(SettingsHelper::SETTINGS_PROCESS_ORDER_IMPORT))
			{
				$service->run($this->lastRun(), date('Y-m-d H:i:s'));

				$this->saveLastRun();
			}
		}
		catch(\Exception $ex)
		{
			$this->getLogger(__FUNCTION__)->error('Etsy::order.orderImportError', $ex->getMessage());
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
