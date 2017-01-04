<?php

namespace Etsy\Services\Shipping;

use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Plugin\ConfigRepository;

use Etsy\Helper\SettingsHelper;
use Etsy\Api\Services\ShippingTemplateService;

/**
 * Class ShippingProfileImportService
 */
class ShippingProfileImportService
{
	/**
	 * @var SettingsRepositoryContract $settingsRepository
	 */
	private $settingsRepository;

	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @var ShippingTemplateService
	 */
	private $shippingTemplateService;

	/**
	 * @var SettingsHelper
	 */
	private $settingsHelper;

	/**
	 * @var array
	 */
	private $currentShippingProfiles = [];

	/**
	 * @param SettingsRepositoryContract $settingsRepository
	 * @param ConfigRepository           $config
	 * @param ShippingTemplateService    $shippingTemplateService
	 * @param SettingsHelper             $settingsHelper
	 */
	public function __construct(SettingsRepositoryContract $settingsRepository, ConfigRepository $config, ShippingTemplateService $shippingTemplateService, SettingsHelper $settingsHelper)
	{
		$this->settingsRepository      = $settingsRepository;
		$this->config                  = $config;
		$this->shippingTemplateService = $shippingTemplateService;
		$this->settingsHelper          = $settingsHelper;
	}

	/**
	 * @return void
	 */
	public function run()
	{
		$shippingProfiles = $this->shippingTemplateService->findAllUserShippingProfiles('__SELF__', $this->settingsHelper->getShopSettings('shopLanguage', 'de'));

		$this->loadAllShippingProfiles();

		foreach($shippingProfiles as $shippingProfile)
		{
			if(is_array($shippingProfile) && isset($shippingProfile['shipping_template_id']))
			{
				$data = [
					'id'                         => $shippingProfile['shipping_template_id'],
					'title'                      => $shippingProfile['title'],
					'minProcessingDays'          => $shippingProfile['min_processing_days'],
					'maxProcessingDays'          => $shippingProfile['max_processing_days'],
					'processingDaysDisplayLabel' => $shippingProfile['processing_days_display_label'],
					'originCountryId'            => $shippingProfile['origin_country_id'],
					'shippingInfo'               => $this->getShippingInfo($shippingProfile),
					'upgrades'                   => $this->getShippingUpgrade($shippingProfile),
				];

				if($this->isImported($shippingProfile['shipping_template_id']))
				{
					$settings = $this->currentShippingProfiles[ $shippingProfile['shipping_template_id'] ];

					$this->settingsRepository->update($data, $settings->id);
				}
				else
				{
					$settings = $this->settingsRepository->create(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_SHIPPING, $data);

					$this->currentShippingProfiles[ $settings->settings['id'] ] = $settings;
				}
			}
		}

		$this->removeDeprecated($shippingProfiles);
	}

	/**
	 * Load all shipping profiles that are currently imported.
	 */
	private function loadAllShippingProfiles()
	{
		$shippingProfileSettings = $this->settingsRepository->find(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_SHIPPING);

		if(count($shippingProfileSettings))
		{
			foreach($shippingProfileSettings as $shippingProfileSetting)
			{
				$this->currentShippingProfiles[ $shippingProfileSetting->settings['id'] ] = $shippingProfileSetting;
			}
		}
	}

	/**
	 * Check if shipping profile id can be imported.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	private function isImported($id):bool
	{
		if(isset($this->currentShippingProfiles[ $id ]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Get the shipping info.
	 *
	 * @param array $shippingProfile
	 *
	 * @return array
	 */
	private function getShippingInfo(array $shippingProfile)
	{
		$list = [];

		if(array_key_exists('Entries', $shippingProfile))
		{
			$entries = $shippingProfile['Entries'];

			if(is_array($entries))
			{
				foreach($entries as $shippingInfo)
				{
					$list[ (int) $shippingInfo['shipping_template_entry_id'] ] = [
						'shippingTemplateEntryId' => $shippingInfo['shipping_template_entry_id'],
						'currency'                => $shippingInfo['currency_code'],
						'originCountryId'         => $shippingInfo['origin_country_id'],
						'destinationCountryId'    => $shippingInfo['destination_country_id'],
						'destinationRegionId'     => $shippingInfo['destination_region_id'],
						'primaryCost'             => $shippingInfo['primary_cost'],
						'secondaryCost'           => $shippingInfo['secondary_cost'],
					];
				}
			}
		}

		return $list;
	}

	/**
	 * Get the shipping upgrade data.
	 *
	 * @param array $shippingProfile
	 *
	 * @return array
	 */
	private function getShippingUpgrade(array $shippingProfile)
	{
		$list = [];

		if(array_key_exists('Upgrades', $shippingProfile))
		{
			$upgrades = $shippingProfile['Upgrades'];

			if(is_array($upgrades))
			{
				foreach($upgrades as $upgrade)
				{
					$list[ (int) $upgrade['value_id'] ] = [
						'valueId'        => $upgrade['value_id'],
						'value'          => $upgrade['value'],
						'price'          => $upgrade['price'],
						'secondaryPrice' => $upgrade['secondary_price'],
						'currencyCode'   => $upgrade['currency_code'],
						'type'           => $upgrade['type'],
						'order'          => $upgrade['order'],
						'language'       => $upgrade['language'],
					];
				}
			}
		}

		return $list;
	}

	/**
	 * Remove deprecated shipping profiles.
	 *
	 * @param array $shippingProfiles
	 */
	private function removeDeprecated($shippingProfiles)
	{
		$shippingProfileList = [];

		foreach($shippingProfiles as $shippingProfile)
		{
			$shippingProfileList[ $shippingProfile['shipping_template_id'] ] = $shippingProfile;
		}

		foreach($this->currentShippingProfiles as $id => $shippingProfileSetting)
		{
			if(!isset($shippingProfileList[ $id ]))
			{
				$this->settingsRepository->delete($shippingProfileSetting->id);
			}
		}
	}
}
