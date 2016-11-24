<?php

namespace Etsy\Controllers;

use Etsy\Services\Property\PropertyImportService;
use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class PropertyController
 */
class PropertyController extends Controller
{
	/**
	 * Import market properties.
	 *
	 * @param PropertyImportService $service
	 * @param Request $request
	 */
	public function import(PropertyImportService $service, Request $request)
	{
		$service->run($request->get('properties', ['occasion', 'when_made', 'recipient', 'who_made']), (bool) $request->get('force', false) === "true");
	}

	/**
	 * Get the imported shipping profiles.
	 *
	 * @return array
	 */
	public function imported()
	{
		$nameList = [];

		/** @var SettingsRepositoryContract $settingsRepository */
		$settingsRepository = pluginApp(SettingsRepositoryContract::class);

		$list = $settingsRepository->find('EtsyIntegrationPlugin', SettingsCorrelationFactory::TYPE_PROPERTY);

		if(count($list))
		{
			/** @var Settings $settings */
			foreach($list as $settings)
			{
				if(isset($settings->settings['id']) && isset($settings->settings['title']))
				{
					$nameList[] = [
						'id'   => $settings->id,
						'name' => $settings->settings['title'],
					];
				}
			}
		}

		return $nameList;
	}
}