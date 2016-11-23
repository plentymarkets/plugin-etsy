<?php
namespace Etsy\Migrations;

use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Order\Referrer\Contracts\OrderReferrerRepositoryContract;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

/**
 * Class CreateOrderReferrer
 */
class CreateOrderReferrer
{
	/**
	 * @var SettingsHelper
	 */
	private $settingsHelper;

	public function __construct(SettingsHelper $settingsHelper)
	{
		$this->settingsHelper = $settingsHelper;
	}

	/**
	 * @param Migrate $migrate
	 */
	public function run(Migrate $migrate)
	{
		/** @var OrderReferrerRepositoryContract $orderReferrerRepo */
		$orderReferrerRepo = pluginApp(OrderReferrerRepositoryContract::class);

		$orderReferrer = $orderReferrerRepo->create([
			                                            'editable'    => false,
			                                            'backendName' => 'Etsy',
			                                            'name'        => 'Etsy',
			                                            'origin'      => 'EtsyIntegrationPlugin',
		                                            ]);

		$this->settingsHelper->save(SettingsHelper::SETTINGS_ORDER_REFERRER, $orderReferrer->id);
	}
}