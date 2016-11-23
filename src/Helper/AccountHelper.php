<?php

namespace Etsy\Helper;

use Etsy\Models\Settings;
use Plenty\Plugin\Application;

use Etsy\Helper\SettingsHelper;

/**
 * Class AccountHelper
 */
class AccountHelper
{
	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @var SettingsHelper
	 */
	private $settingsHelper;

	/**
	 * @param Application $app
	 * @param SettingsHelper $settingsHelper
	 */
	public function __construct(Application $app, SettingsHelper $settingsHelper)
	{
		$this->app = $app;
		$this->settingsHelper = $settingsHelper;
	}

	/**
	 * Get the access token data.
	 *
	 * @return array
	 */
	public function getTokenData()
	{
		$settings = $this->settingsHelper->get(SettingsHelper::SETTINGS_ACCESS_TOKEN);

		if($settings)
		{
			$data = json_decode($settings->value, true);

			return [
				'accessToken'       => isset($data['accessToken']) ? $data['accessToken'] : '',
				'accessTokenSecret' => isset($data['accessTokenSecret']) ? $data['accessTokenSecret'] : '',
			];
		}
	}

	/**
	 * Get the consumer key.
	 *
	 * @return string
	 */
	public function getConsumerKey()
	{
		return '6d6s53b0qd09nhw37253ero8';
	}

	/**
	 * Get the consumer shared secret.
	 *
	 * @return string
	 */
	public function getConsumerSecret()
	{
		return 'dzi5pnxwxm';
	}

	/**
	 * Get the token request data.
	 *
	 * @return null|Settings
	 */
	public function getTokenRequest()
	{
		$settings = $this->settingsHelper->get(SettingsHelper::SETTINGS_TOKEN_REQUEST);

		if($settings)
		{
			return $settings;
		}

		return null;
	}

	/**
	 * Save the token request data.
	 *
	 * @param $data
	 */
	public function saveTokenRequest($data)
	{
		$this->settingsHelper->save(SettingsHelper::SETTINGS_TOKEN_REQUEST, (string) json_encode($data));
	}

	/**
	 * Save the access token data.
	 *
	 * @param array $data
	 */
	public function saveAccessToken($data)
	{
		$this->settingsHelper->save(SettingsHelper::SETTINGS_ACCESS_TOKEN, (string) json_encode($data));
	}
}