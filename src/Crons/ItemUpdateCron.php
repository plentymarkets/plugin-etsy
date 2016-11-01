<?php

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Batch\Item\ItemUpdateService;

/**
 * Class ItemUpdateCron
 */
class ItemUpdateCron extends Cron
{
	/**
	 * @param ItemUpdateService $service
	 */
    public function handle(ItemUpdateService $service)
    {
        $service->run();
    }
}
