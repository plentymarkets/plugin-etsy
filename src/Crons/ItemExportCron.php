<?php

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Batch\Item\ItemExportService;

/**
 * Class ItemExportCron
 */
class ItemExportCron extends Cron
{
	/**
	 * @param ItemExportService $service
	 */
	public function handle(ItemExportService $service)
	{
		$service->run();
	}
}
