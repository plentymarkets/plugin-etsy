<?php

namespace Etsy\Controllers;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Plugin\Application;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

use Etsy\Models\Settings;

/**
 * Class SettingsController
 */
class SettingsController extends Controller
{
	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var DataBase
	 */
	private $dataBase;

	/**
	 * @var Application
	 */
	private $app;

	public function __construct(Request $request, DataBase $dataBase, Application $app)
	{
		$this->request = $request;
		$this->dataBase = $dataBase;
		$this->app = $app;
	}

	/**
	 * Get all settings.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function get()
	{
		$settings = $this->dataBase->find(Settings::class, 3);

		if($settings instanceof Settings)
		{
			return json_decode($settings->value, true);
		}
		else
		{
			throw new \Exception('Could not load settings.');
		}
	}

	/**
	 * Save all settings.
	 */
	public function save()
	{
		$settingsData = $this->request->get('settings');

		$settings = $this->app->make(Settings::class);

		if($settings instanceof Settings)
		{
			$settings->id = 3;
			$settings->name = 'settings';
			$settings->value = (string) json_encode($settingsData);
			$settings->createdAt = date('Y-m-d H:i:s');
			$settings->updatedAt = date('Y-m-d H:i:s');

			$this->dataBase->save($settings);
		}
	}
}