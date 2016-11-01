<?php

namespace Etsy\Services\Shipping;

use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Modules\Market\Settings\Models\Settings;
use Plenty\Plugin\ConfigRepository;

use Etsy\Logger\Logger;
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
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @var ShippingTemplateService
	 */
	private $shippingTemplateService;

	/**
	 * @param SettingsRepositoryContract $settingsRepository
	 * @param Logger                     $logger
	 * @param ConfigRepository           $config
	 * @param ShippingTemplateService    $shippingTemplateService
	 */
	public function __construct(SettingsRepositoryContract $settingsRepository, Logger $logger, ConfigRepository $config, ShippingTemplateService $shippingTemplateService)
	{
		$this->settingsRepository      = $settingsRepository;
		$this->logger                  = $logger;
		$this->config                  = $config;
		$this->shippingTemplateService = $shippingTemplateService;
	}

	/**
	 * @return void
	 */
	public function run()
	{
		$shippingProfiles = $this->shippingTemplateService->findAllUserShippingProfiles('__SELF__', $this->config->get('EtsyIntegrationPlugin.shopLanguage'));

		foreach($shippingProfiles as $shippingProfile)
		{
			if(is_array($shippingProfile) && isset($shippingProfile['shipping_template_id']) && $this->canImport($shippingProfile['shipping_template_id']))
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

				$this->settingsRepository->create('EtsyIntegrationPlugin', SettingsCorrelationFactory::TYPE_SHIPPING, $data);
			}
		}
	}

	/**
	 * Check if shipping profile id can be imported.
	 *
	 * @param int $id
	 * @return bool
	 */
	private function canImport($id):bool
	{
		$importedShippingProfiles = $this->settingsRepository->find('EtsyIntegrationPlugin', SettingsCorrelationFactory::TYPE_SHIPPING);

		if(count($importedShippingProfiles))
		{
			/** @var Settings $shippingProfile */
			foreach($importedShippingProfiles as $shippingProfile)
			{
				if(isset($shippingProfile->settings['id']) && $shippingProfile->settings['id'] == $id)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Get the shipping info.
	 *
	 * @param array $shippingProfile
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
}
