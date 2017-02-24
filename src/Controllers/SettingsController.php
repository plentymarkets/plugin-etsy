<?php

namespace Etsy\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

use Etsy\Helper\SettingsHelper;

/**
 * Class SettingsController
 */
class SettingsController extends Controller
{
	/**
	 * @var SettingsHelper
	 */
	private $settingsHelper;

	/**
	 * @param SettingsHelper $settingsHelper
	 */
	public function __construct(SettingsHelper $settingsHelper)
	{
		$this->settingsHelper = $settingsHelper;
	}

	/**
	 * Get all settings.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function all()
	{
		$data = $this->settingsHelper->get(SettingsHelper::SETTINGS_SETTINGS);

		if($data)
		{
			return json_decode($data, true);
		}

		return [
			'shop' => [
				'shopId' => null,
				'mainLanguage' => 'de',
				'exportLanguages' => [],
				'processes' => []
			],
		];
	}

	/**
	 * Save all settings.
	 */
	public function save()
	{
		$this->settingsHelper->save(SettingsHelper::SETTINGS_SETTINGS, (string) json_encode([
			                                                                'shop'    => pluginApp(Request::class)->get('shop', []),
		                                                                ]));

		return pluginApp(Response::class)->make('', 204);
	}

	/**
	 * Get all Etsy shops.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getShops()
	{
		$data = $this->settingsHelper->get(SettingsHelper::SETTINGS_ETSY_SHOPS);

		if($data)
		{
			return json_decode($data, true);
		}

		return [];
	}
}