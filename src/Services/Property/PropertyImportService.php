<?php

namespace Etsy\Services\Property;

use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;

use Etsy\Api\Services\DataTypeService;

/**
 * Class PropertyImportService
 */
class PropertyImportService
{
	/**
	 * @var DataTypeService
	 */
	private $dataTypeService;

	/**
	 * @var SettingsRepositoryContract
	 */
	private $settingsRepository;

	/**
	 * @var array
	 */
	private $currentProperties = [];

	/**
	 * @param DataTypeService $dataTypeService
	 * @param SettingsRepositoryContract $settingsRepository
	 */
	public function __construct(DataTypeService $dataTypeService, SettingsRepositoryContract $settingsRepository)
	{
		$this->dataTypeService = $dataTypeService;
		$this->settingsRepository = $settingsRepository;
	}

	/**
	 * Import the properties.
	 *
	 * @param array $properties
	 * @param bool $force
	 */
	public function run($properties, $force = false)
	{
		if($force)
		{
			$this->settingsRepository->deleteAll('EtsyIntegrationPlugin', SettingsCorrelationFactory::TYPE_PROPERTY);
		}

		$this->loadAllProperties();

		if(is_array($properties) && count($properties))
		{
			$languages = ['de', 'en'];

			foreach ($properties as $propertyKey)
			{
				try
				{
					$enum = [];

					foreach ($languages as $language)
					{
						if($propertyKey == 'when_made')
						{
							$enum[$language] = $this->dataTypeService->describeWhenMadeEnum($language);
						}
						elseif($propertyKey == 'who_made')
						{
							$enum[$language] = $this->dataTypeService->describeWhoMadeEnum($language);
						}
						elseif($propertyKey == 'occasion')
						{
							$enum[$language] = $this->dataTypeService->describeOccasionEnum($language);
						}
						elseif($propertyKey == 'recipient')
						{
							$enum[$language] = $this->dataTypeService->describeRecipientEnum($language);
						}
					}

					if(is_array($enum) && count($enum))
					{
						$this->createSettings($propertyKey, $enum);
					}
				}
				catch(\Exception $ex)
				{
					// TODO maybe log?
				}
			}
		}
	}

	/**
	 * Load all properties that are currently imported.
	 */
	private function loadAllProperties()
	{
		$propertySettings = $this->settingsRepository->find('EtsyIntegrationPlugin', SettingsCorrelationFactory::TYPE_PROPERTY);

		if (count($propertySettings))
		{
			foreach($propertySettings as $propertySetting)
			{
				$this->currentProperties[$propertySetting->settings['propertyKey']][$this->calculateHash($propertySetting->settings)] = $propertySetting->settings;
			}
		}
	}

	/**
	 * Create settings for a given property key and enums.
	 *
	 * @param string $propertyKey
	 * @param array $propertyEnum
	 */
	private function createSettings($propertyKey, $propertyEnum)
	{
		$defaultPropertyEnum = reset($propertyEnum);

		if (isset($defaultPropertyEnum['values']) && is_array($defaultPropertyEnum['values']) && count($defaultPropertyEnum['values']))
		{
			foreach ($defaultPropertyEnum['values'] as $propertyValueKey)
			{
				$data = [
					'propertyKey'      => $propertyKey,
					'propertyValueKey' => $propertyValueKey,
				];

				foreach (array_keys($propertyEnum) as $lang)
				{
					$data['propertyName'][$lang] = $this->getPropertyName($propertyKey, $lang);

					if (isset($propertyEnum[$lang]['formatted']) && is_array($propertyEnum[$lang]['formatted']) && isset($propertyEnum[$lang]['formatted'][$propertyValueKey]) && strlen($propertyEnum[$lang]['formatted'][$propertyValueKey]))
					{
						$data['propertyValueName'][$lang] = $propertyEnum[$lang]['formatted'][$propertyValueKey];
					} else
					{
						$data['propertyValueName'][$lang] = $this->formatName($propertyValueKey);
					}
				}

				if (!$this->isImported($propertyKey, $data))
				{
					$this->settingsRepository->create('EtsyIntegrationPlugin', SettingsCorrelationFactory::TYPE_PROPERTY, $data);
				}
			}
		}
	}

	/**
	 * Get the property name.
	 *
	 * @param string $propertyKey
	 * @param string $lang
	 *
	 * @return string
	 */
	private function getPropertyName($propertyKey, $lang)
	{
		$map = [
			'occasion' => [
				'en' => 'Occasion',
				'de' => 'Anlass',
			],

			'when_made' => [
				'en' => 'When made',
				'de' => 'Hergestellt',
			],

			'who_made' => [
				'en' => 'Who made',
				'de' => 'Hersteller',
			],

			'recipient' => [
				'en' => 'Recipient',
				'de' => 'Empfänger',
			]
		];

		if (isset($map[$propertyKey]) && isset($map[$propertyKey][$lang]) && strlen($map[$propertyKey][$lang]))
		{
			return $map[$propertyKey][$lang];
		}

		return $this->formatName($propertyKey);
	}

	/**
	 * Format a name.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	private function formatName($name)
	{
		return ucfirst(str_replace('_', ' ', $name));
	}

	/**
	 * Check if property is already imported.
	 *
	 * @param string $propertyKey
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	private function isImported($propertyKey, $data)
	{
		if (isset($this->currentProperties[$propertyKey]) && isset($this->currentProperties[$propertyKey][$this->calculateHash($data)]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Calculate hash value for array.
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	private function calculateHash($data)
	{
		return md5(serialize($data));
	}
}
