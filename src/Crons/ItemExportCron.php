<?php

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Batch\Item\ItemExportService;
use Etsy\Helper\AccountHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ItemExportCron
 */
class ItemExportCron extends Cron
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
	 * Run the item export process.
	 *
	 * @param ItemExportService $service
	 * @param AccountHelper     $accountHelper
	 */
	public function handle(ItemExportService $service, AccountHelper $accountHelper)
	{
		try
		{
			if($accountHelper->isProcessActive(SettingsHelper::SETTINGS_PROCESS_ITEM_EXPORT))
			{
				$service->run([
					              'lastRun' => $this->lastRun(),
				              ]);

				$this->saveLastRun();
			}
		}
		catch(\Exception $ex)
		{
			$this->getLogger(__FUNCTION__)->error('Etsy::item.itemExportError', $ex);
		}
	}

	/**
	 * Get the last run.
	 *
	 * @return string|null
	 */
	private function lastRun()
	{
		return $this->settingsHelper->get(SettingsHelper::SETTINGS_LAST_ITEM_EXPORT);
	}

	/**
	 * Save the last run.
	 */
	private function saveLastRun()
	{
		$this->settingsHelper->save(SettingsHelper::SETTINGS_LAST_ITEM_EXPORT, date('Y-m-d H:i:s'));
	}
}
