<?php

namespace Etsy\Controllers;

use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Modules\Market\Settings\Models\Settings;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Plugin\Controller;
use Etsy\Services\Shipping\ShippingProfileImportService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class ShippingProfileController
 */
class ShippingProfileController extends Controller
{
	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param Request     $request
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Get the imported shipping profiles.
	 *
	 * @return array
	 */
	public function imported()
	{
		$nameList = [];

		/** @var SettingsRepositoryContract $marketSettingsRepository */
		$marketSettingsRepository = pluginApp(SettingsRepositoryContract::class);

		$list = $marketSettingsRepository->find(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_SHIPPING);

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

	/**
	 * Import shipping profiles.
	 *
	 * @param ShippingProfileImportService $service
	 *
	 * @return void
	 */
	public function import(ShippingProfileImportService $service)
	{
		$service->run();

		return pluginApp(Response::class)->make('', 204);
	}

	/**
	 * Get the shipping profile correlations.
	 *
	 * @param SettingsCorrelationFactory $settingsCorrelationFactory
	 *
	 * @return array
	 */
	public function correlations(SettingsCorrelationFactory $settingsCorrelationFactory)
	{
		$correlations = $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_SHIPPING)->all(SettingsHelper::PLUGIN_NAME);

		return $correlations;
	}

	/**
	 * Get the parcel service presets.
	 *
	 * @return array
	 */
	public function parcelServicePresets():array
	{
		$nameList = [];

		/** @var ParcelServicePresetRepositoryContract $parcelServicePresetRepository */
		$parcelServicePresetRepository = pluginApp(ParcelServicePresetRepositoryContract::class);

		$list = $parcelServicePresetRepository->getPresetList();

		if(count($list))
		{
			foreach($list as $parcelServicePreset)
			{
				$nameList[] = [
					'id'   => $parcelServicePreset->id,
					'name' => $parcelServicePreset->backendName,
				];
			}
		}

		return $nameList;
	}


	/**
	 * Correlate settings IDs with an parcel service preset IDs.
	 *
	 * @param SettingsCorrelationFactory $settingsCorrelationFactory
	 */
	public function correlate(SettingsCorrelationFactory $settingsCorrelationFactory)
	{
		$settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_SHIPPING)->clear(SettingsHelper::PLUGIN_NAME);

		foreach($this->request->get('correlations', []) as $correlationData)
		{
			if(isset($correlationData['settingsId']) && $correlationData['settingsId'] && isset($correlationData['parcelServicePresetId']) && $correlationData['parcelServicePresetId'])
			{
				$settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_SHIPPING)->createRelation($correlationData['settingsId'], $correlationData['parcelServicePresetId']);
			}
		}

		return pluginApp(Response::class)->make('', 204);
	}
}
