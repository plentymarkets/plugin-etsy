<?php

namespace Etsy\Crons;

use Carbon\Carbon;
use Etsy\EtsyServiceProvider;
use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Services\Batch\Item\ItemExportService;
use Etsy\Helper\AccountHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\ConfigRepository;

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
    private $configRepository;

    /**
	 * @param SettingsHelper $settingsHelper
	 */
	public function __construct(SettingsHelper $settingsHelper, ConfigRepository $configRepository)
	{
		$this->settingsHelper = $settingsHelper;
        $this->configRepository = $configRepository;
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
            if($this->checkIfCanRun() === 'true') return;

            if($accountHelper->isProcessActive(SettingsHelper::SETTINGS_PROCESS_ITEM_EXPORT))
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
			$this->getLogger(EtsyServiceProvider::ITEM_EXPORT_CRON)->error('Etsy::item.itemExportError', $ex->getMessage());
		}
	}

    /**
     * Return if we can run this cron or is disabled
     *
     * @return string
     */
    private function checkIfCanRun(): string
    {
        return $this->configRepository->get(SettingsHelper::PLUGIN_NAME . '.listings', 'true');
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
