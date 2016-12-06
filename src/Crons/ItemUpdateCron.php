<?php

namespace Etsy\Crons;

use Etsy\Services\Batch\Item\ItemUpdateStockService;
use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

/**
 * Class ItemUpdateCron
 */
class ItemUpdateCron extends Cron
{
	/**
	 * @param ItemUpdateStockService $service
	 */
    public function handle(ItemUpdateStockService $service)
    {
        $service->run();
    }
}
