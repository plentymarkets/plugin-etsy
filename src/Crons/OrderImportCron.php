<?php

namespace Etsy\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Etsy\Helper\SettingsHelper;
use Etsy\Helper\AccountHelper;
use Etsy\Services\Order\OrderImportService;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\ConfigRepository;


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
	 * @var ConfigRepository
	 */
	private $config;

    /**
     * @param SettingsHelper $settingsHelper
     * @param ConfigRepository $config
     */
	public function __construct(SettingsHelper $settingsHelper, ConfigRepository $config)
	{
		$this->settingsHelper = $settingsHelper;
		$this->config = $config;
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
            $this->getLogger(__FUNCTION__)
                 ->report('OrderImportStarted', [
                     'config'   => $this->config->get(SettingsHelper::PLUGIN_NAME),
                     'from'     => 'plugin'
                 ]);

            if($this->checkIfCanRun() == 'true') return;

			if($accountHelper->isProcessActive(SettingsHelper::SETTINGS_PROCESS_ORDER_IMPORT))
			{
                $from = $this->lastRun();
                $to = date('Y-m-d H:i:s');

				$service->run($from, $to);

				$this->saveLastRun($to);
			}
		}
		catch(\Exception $ex)
		{
			$this->getLogger(__FUNCTION__)->error('Etsy::order.orderImportError', $ex->getMessage());
		}
	}

    /**
     * Return if we can run this cron or is disabled
     *
     * @return bool
     */
    private function checkIfCanRun(): bool
    {
        return $this->config->get(SettingsHelper::PLUGIN_NAME . '.orderImport', 'true');
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
     * @param $to
     */
	private function saveLastRun($to)
	{
		$this->settingsHelper->save(SettingsHelper::SETTINGS_LAST_ORDER_IMPORT, $to);
	}
}
