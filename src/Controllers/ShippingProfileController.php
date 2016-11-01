<?php

namespace Etsy\Controllers;

use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Modules\Market\Settings\Models\Settings;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\Controller;
use Etsy\Services\Shipping\ShippingProfileImportService;

/**
 * Class ShippingProfileController
 */
class ShippingProfileController extends Controller
{
	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
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
		$marketSettingsRepository = $this->app->make(SettingsRepositoryContract::class);

		$list = $marketSettingsRepository->find('EtsyIntegrationPlugin', SettingsCorrelationFactory::TYPE_SHIPPING);

		if(count($list))
		{
			/** @var Settings $settings */
			foreach($list as $settings)
			{
				if(isset($settings->settings['id']) && isset($settings->settings['title']))
				{
					$nameList[$settings->id] = $settings->settings['title'];
				}
			}
		}
		return $nameList;
	}

	/**
	 * Import shipping profiles.
	 *
	 * @param ShippingProfileImportService $service
	 * @return void
	 */
	public function import(ShippingProfileImportService $service)
	{
		$service->run();
	}

	/**
	 * Get the shipping profile correlations.
	 *
	 * @param SettingsCorrelationFactory $settingsCorrelationFactory
	 * @return array
	 */
	public function correlations(SettingsCorrelationFactory $settingsCorrelationFactory)
	{
		$correlations = $settingsCorrelationFactory
			->type(SettingsCorrelationFactory::TYPE_SHIPPING)
			->all('EtsyIntegrationPlugin');

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
		$parcelServicePresetRepository = $this->app->make(ParcelServicePresetRepositoryContract::class);

		$list = $parcelServicePresetRepository->getPresetList();

		if(count($list))
		{
			foreach($list as $parcelServicePreset)
			{
				$nameList[$parcelServicePreset->id] = $parcelServicePreset->backendName;
			}
		}

		return $nameList;
	}


	/**
	 * Correlate an settings ID with an parcel service preset ID.
	 *
	 * @param int $settingsId
	 * @param int $parcelServicePresetId
	 * @param SettingsCorrelationFactory $settingsCorrelationFactory
	 */
	public function correlate($settingsId, $parcelServicePresetId, SettingsCorrelationFactory $settingsCorrelationFactory)
	{
		$settingsCorrelationFactory
			->type(SettingsCorrelationFactory::TYPE_SHIPPING)
			->createRelation($settingsId, $parcelServicePresetId);
	}
}
