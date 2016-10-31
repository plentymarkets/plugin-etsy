<?php

namespace Etsy\Services\Shipping;

use Plenty\Plugin\ConfigRepository;

use Etsy\Logger\Logger;
use Etsy\Api\Services\ShippingTemplateService;
use Etsy\Contracts\ShippingProfileRepositoryContract;

/**
 * Class ShippingProfileImportService
 */
class ShippingProfileImportService
{
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
	 * @var ShippingProfileRepositoryContract
	 */
	private $shippingProfileRepository;

	/**
	 * @param Logger                            $logger
	 * @param ConfigRepository                  $config
	 * @param ShippingTemplateService           $shippingTemplateService
	 * @param ShippingProfileRepositoryContract $shippingProfileRepository
	 */
	public function __construct(Logger $logger, ConfigRepository $config, ShippingTemplateService $shippingTemplateService, ShippingProfileRepositoryContract $shippingProfileRepository)
	{
		$this->logger                    = $logger;
		$this->config                    = $config;
		$this->shippingTemplateService   = $shippingTemplateService;
		$this->shippingProfileRepository = $shippingProfileRepository;
	}

	/**
	 * @return void
	 */
	public function run()
	{
		$shippingProfiles = $this->shippingTemplateService->findAllUserShippingProfiles('__SELF__', $this->config->get('EtsyIntegrationPlugin.shopLanguage'));

		foreach($shippingProfiles as $shippingProfile)
		{
			if(is_array($shippingProfile))
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

				$this->shippingProfileRepository->create($data);
			}
		}
	}

	/**
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
						'shippingTtemplateEntryId' => $shippingInfo['shipping_template_entry_id'],
						'currency'                 => $shippingInfo['currency_code'],
						'originCountryId'          => $shippingInfo['origin_country_id'],
						'destinationCountryId'     => $shippingInfo['destination_country_id'],
						'destinationRegionId'      => $shippingInfo['destination_region_id'],
						'primaryCost'              => $shippingInfo['primary_cost'],
						'secondaryCost'            => $shippingInfo['secondary_cost'],
					];
				}
			}
		}

		return $list;
	}

	/**
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
