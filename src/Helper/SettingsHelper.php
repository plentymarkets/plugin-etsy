<?php

namespace Etsy\Helper;

use Etsy\Models\Settings;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;

/**
 * Class SettingsHelper
 */
class SettingsHelper
{
	const SETTINGS_TOKEN_REQUEST = 1;
	const SETTINGS_ACCESS_TOKEN = 2;
	const SETTINGS_SETTINGS = 3;
	const SETTINGS_ORDER_REFERRER = 4;

	private $settingsMap = [
		self::SETTINGS_TOKEN_REQUEST  => 'token_request',
		self::SETTINGS_ACCESS_TOKEN   => 'access_token',
		self::SETTINGS_SETTINGS       => 'settings',
		self::SETTINGS_ORDER_REFERRER => 'order_referrer',
	];

	/**
	 * @var DataBase
	 */
	private $dataBase;

	/**
	 * @param DataBase $dataBase
	 */
	public function __construct(DataBase $dataBase)
	{
		$this->dataBase = $dataBase;
	}

	/**
	 * Save settings to database.
	 *
	 * @param int    $id
	 * @param string $value
	 */
	public function save($id, $value)
	{
		$settings = pluginApp(Settings::class);

		if($settings instanceof Settings)
		{
			$settings->id        = $id;
			$settings->name      = $this->settingsMap[$id];
			$settings->value     = $value;
			$settings->createdAt = date('Y-m-d H:i:s');
			$settings->updatedAt = date('Y-m-d H:i:s');

			$this->dataBase->save($settings);
		}
	}

	/**
	 * Get settings for a given id.
	 *
	 * @param int $id
	 *
	 * @return Settings|null
	 */
	public function get($id)
	{
		$settings = $this->dataBase->find(Settings::class, $id);

		if($settings instanceof Settings)
		{
			return $settings;
		}

		return null;
	}
}
