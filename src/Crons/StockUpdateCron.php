<?php

namespace Etsy\Crons;

use Carbon\Carbon;
use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Batch\Item\ItemUpdateStockService;
use Etsy\Helper\AccountHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\ConfigRepository;

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
     * @var ConfigRepository
     */
    private ConfigRepository $configRepository;

    /**
     * @param SettingsHelper $settingsHelper
     * @param ConfigRepository $configRepository
     */
	public function __construct(SettingsHelper $settingsHelper, ConfigRepository $configRepository)
	{
		$this->settingsHelper = $settingsHelper;
        $this->configRepository = $configRepository;
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
            if($this->checkIfCanRun() === 'true') return;

			if($accountHelper->isProcessActive(SettingsHelper::SETTINGS_PROCESS_STOCK_UPDATE))
			{
                $lastRun = $this->lastRun();

                if ($lastRun) {
                    /** @var Carbon $lastRun */
                    $lastRun = pluginApp(Carbon::class, [$lastRun]);
                }

                $service->run($lastRun);

                $this->saveLastRun();
			}
		}
		catch(\Exception $ex)
		{
			$this->getLogger(__FUNCTION__)->error('Etsy::item.stockUpdateError', $ex->getMessage());
		}
	}

    /**
     * Return if we can run this cron or is disabled
     *
     * @return string
     */
    private function checkIfCanRun(): string
    {
        return $this->configRepository->get(SettingsHelper::PLUGIN_NAME . '.stockUpdate', 'true');
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
