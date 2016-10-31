<?php

namespace Etsy\Helper;

use Etsy\Models\Settings;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Plugin\Application;

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
	 * @var DataBase
	 */
	private $dataBase;

	/**
	 * @param Application $app
	 * @param DataBase $dataBase
	 */
	public function __construct(Application $app, DataBase $dataBase)
	{
		$this->app = $app;
		$this->dataBase = $dataBase;
	}

	/**
	 * Get the access token data.
	 *
	 * @return array
	 */
	public function getTokenData()
	{
		$settings = $this->dataBase->find(Settings::class, 2);

		if($settings instanceof Settings)
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
}