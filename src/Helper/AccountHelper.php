<?php

namespace Etsy\Helper;

use Plenty\Plugin\Application;

use Etsy\Helper\OrderHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;

/**
 * Class AccountHelper
 */
class AccountHelper
{
	use Loggable;

	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @var SettingsHelper
	 */
	private $settingsHelper;

	/**
	 * @var OrderHelper
	 */
	private $orderHelper;

	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @param Application      $app
	 * @param SettingsHelper   $settingsHelper
	 * @param OrderHelper      $orderHelper
	 * @param ConfigRepository $config
	 */
	public function __construct(Application $app, SettingsHelper $settingsHelper, OrderHelper $orderHelper, ConfigRepository $config)
	{
		$this->app            = $app;
		$this->settingsHelper = $settingsHelper;
		$this->orderHelper    = $orderHelper;
		$this->config         = $config;
	}

	/**
	 * Get the access token data.
	 *
	 * @return array
	 */
	public function getTokenData()
	{
		$data = $this->settingsHelper->get(SettingsHelper::SETTINGS_ACCESS_TOKEN);

		if($data)
		{
			$data = json_decode($data, true);

			return [
				'accessToken'       => isset($data['accessToken']) ? $data['accessToken'] : '',
				'accessTokenSecret' => isset($data['accessTokenSecret']) ? $data['accessTokenSecret'] : '',
				'consumerKey'       => isset($data['consumerKey']) ? $data['consumerKey'] : '',
				'consumerSecret'    => isset($data['consumerSecret']) ? $data['consumerSecret'] : '',
			];
		}
	}

	/**
	 * Get the consumer key.
	 *
	 * @return string
	 */
	public function getConsumerKey():string
	{
		return (string) $this->config->get('Etsy.consumerKey');
	}

	/**
	 * Get the consumer shared secret.
	 *
	 * @return string
	 */
	public function getConsumerSecret():string
	{
		return (string) $this->config->get('Etsy.consumerSecret');
	}

	/**
	 * Get the token request data.
	 *
	 * @return null|array
	 */
	public function getTokenRequest()
	{
		$data = $this->settingsHelper->get(SettingsHelper::SETTINGS_TOKEN_REQUEST);

		if($data)
		{
			return json_decode($data, true);
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

	/**
	 * Validates the Etsy configuration settings.
	 *
	 * @return bool
	 */
	public function isValidConfig():bool
	{
		try
		{
			$tokenData  = $this->getTokenData();
			$shopId     = $this->settingsHelper->getShopSettings('shopId');
			$referrerId = $this->orderHelper->getReferrerId();

			if($tokenData && isset($tokenData['accessToken']) && $shopId && $shopId > 0 && $referrerId && $referrerId > 0)
			{
				return true;
			}
		}
		catch(\Exception $ex)
		{
			$this->getLogger(__FUNCTION__)->error('Etsy::authentication.configValidationError', $ex);
		}

		return false;
	}

	/**
	 * Checks whether the given process is active.
	 *
	 * @param string $processKey
	 *
	 * @return bool
	 */
	public function isProcessActive($processKey):bool
	{
		return array_search($processKey, $this->settingsHelper->getShopSettings('processes', [])) !== false;
	}
}