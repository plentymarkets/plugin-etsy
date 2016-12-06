<?php

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Batch\Item\ItemUpdateStockService;
use Etsy\Helper\AccountHelper;
use Etsy\Helper\SettingsHelper;

/**
 * Class StockUpdateCron
 */
class StockUpdateCron extends Cron
{
	/**
	 * Run the stock update process.
	 *
	 * @param ItemUpdateStockService $service
	 * @param AccountHelper $accountHelper
	 */
    public function handle(ItemUpdateStockService $service, AccountHelper $accountHelper)
    {
	    if($accountHelper->isProcessActive(SettingsHelper::SETTINGS_PROCESS_STOCK_UPDATE))
	    {
		    $service->run();
	    }
    }
}
