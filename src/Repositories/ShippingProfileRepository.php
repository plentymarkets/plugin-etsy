<?php
namespace Etsy\Repositories;

use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Market\Settings\Models\Settings;

use Etsy\Contracts\ShippingProfileRepositoryContract;

/**
 * Class ShippingProfileRepository
 */
class ShippingProfileRepository implements ShippingProfileRepositoryContract
{
	/**
	 * @var SettingsRepositoryContract
	 */
	private $settingsRepository;

	/**
	 * @var SettingsCorrelationFactory
	 */
	private $settingsCorrelation;

	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @param SettingsRepositoryContract $settingsRepository
	 * @param SettingsCorrelationFactory $settingsCorrelation
	 * @param ConfigRepository           $config
	 */
	public function __construct(SettingsRepositoryContract $settingsRepository, SettingsCorrelationFactory $settingsCorrelation, ConfigRepository $config)
	{
		$this->settingsRepository  = $settingsRepository;
		$this->settingsCorrelation = $settingsCorrelation;
		$this->config              = $config;
	}

	/**
	 * @param int $id
	 * @return Settings
	 */
	public function show($id)
	{
		return $this->settingsRepository->get($id);
	}


	/**
	 * @param array $data
	 * @return null|Settings
	 */
	public function create(array $data)
	{
		if($settings = $this->getShippingProfileById((int) $data['id']))
		{
			return $settings;
		}

		$settings = $this->settingsRepository->create($this->config->get('EtsyIntegrationPlugin.marketplaceId'), SettingsCorrelationFactory::TYPE_SHIPPING, $data);

		return $settings;
	}

	/**
	 * @param int $settingsId
	 * @param int $parcelServicePresetId
	 * @return void
	 */
	public function createRelation($settingsId, $parcelServicePresetId)
	{
		$this->settingsCorrelation->type(SettingsCorrelationFactory::TYPE_SHIPPING)->createRelation($settingsId, $parcelServicePresetId);
	}

	/**
	 * @param int $shippingProfileId
	 * @return null|Settings
	 */
	private function getShippingProfileById($shippingProfileId)
	{
		$list = $this->settingsRepository->find($this->config->get('EtsyIntegrationPlugin.marketplaceId'), SettingsCorrelationFactory::TYPE_SHIPPING);

		foreach($list as $settings)
		{
			$shippingProfileSettings = $settings->settings;

			if(is_array($shippingProfileSettings) && (int) $shippingProfileSettings['id'] == $shippingProfileId)
			{
				return $settings;
			}
		}

		return null;
	}
}
