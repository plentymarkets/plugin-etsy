<?php

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Batch\Item\ItemUpdateStockService;
use Etsy\Helper\AccountHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Plugin\Log\Loggable;

/**
 * Class StockUpdateCron
 */
class StockUpdateCron extends Cron
{
	use Loggable;

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
	 * Run the stock update process.
	 *
	 * @param ItemUpdateStockService $service
	 * @param AccountHelper          $accountHelper
	 */
	public function handle(ItemUpdateStockService $service, AccountHelper $accountHelper)
	{
		try
		{
			if($accountHelper->isProcessActive(SettingsHelper::SETTINGS_PROCESS_STOCK_UPDATE))
			{
				$service->run([
					              'lastRun' => $this->lastRun(),
				              ]);

				$this->saveLastRun();
			}
		}
		catch(\Exception $ex)
		{
			$this->getLogger(__FUNCTION__)->error('Etsy::item.stockUpdateError', $ex);
		}
	}

	/**
	 * Get the last run.
	 *
	 * @return string|null
	 */
	private function lastRun()
	{
		return $this->settingsHelper->get(SettingsHelper::SETTINGS_LAST_STOCK_UPDATE);
	}

	/**
	 * Save the last run.
	 */
	private function saveLastRun()
	{
		$this->settingsHelper->save(SettingsHelper::SETTINGS_LAST_STOCK_UPDATE, date('Y-m-d H:i:s'));
	}
}
