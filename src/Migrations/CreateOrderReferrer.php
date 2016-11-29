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

	/**
	 * CreateOrderReferrer constructor.
	 *
	 * @param SettingsHelper $settingsHelper
	 */
	public function __construct(SettingsHelper $settingsHelper)
	{
		$this->settingsHelper = $settingsHelper;
	}

	/**
	 * @param OrderReferrerRepositoryContract $orderReferrerRepo
	 */
	public function run(OrderReferrerRepositoryContract $orderReferrerRepo)
	{
		$orderReferrer = $orderReferrerRepo->create([
			                                            'editable'    => false,
			                                            'backendName' => 'Etsy',
			                                            'name'        => 'Etsy',
			                                            'origin'      => 'EtsyIntegrationPlugin',
		                                            ]);
		$retries = 0;

		do
		{
			// due to the fact that CreateSettingsTable migration just run, it could be that DynamoDB needs some time to create the table, so we try again
			$status = $this->settingsHelper->save(SettingsHelper::SETTINGS_ORDER_REFERRER, $orderReferrer->id);

			if($status === false)
			{
				sleep(300);
			}
		}
		while($status === false || ++$retries < 3);
	}
}