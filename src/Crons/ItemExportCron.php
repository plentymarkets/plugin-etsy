<?php

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Batch\Item\ItemExportService;
use Etsy\Helper\AccountHelper;
use Etsy\Helper\SettingsHelper;

/**
 * Class ItemExportCron
 */
class ItemExportCron extends Cron
{
	/**
	 * Run the item export process.
	 *
	 * @param ItemExportService $service
	 * @param AccountHelper     $accountHelper
	 */
	public function handle(ItemExportService $service, AccountHelper $accountHelper)
	{
		if($accountHelper->isProcessActive(SettingsHelper::SETTINGS_PROCESS_ITEM_EXPORT))
		{
			$service->run();
		}
	}
}
