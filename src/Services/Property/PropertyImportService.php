<?php

namespace Etsy\Services\Property;

use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;

use Etsy\Api\Services\DataTypeService;
use Plenty\Modules\Market\Settings\Models\Settings;

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
	 * @param DataTypeService            $dataTypeService
	 * @param SettingsRepositoryContract $settingsRepository
	 */
	public function __construct(DataTypeService $dataTypeService, SettingsRepositoryContract $settingsRepository)
	{
		$this->dataTypeService    = $dataTypeService;
		$this->settingsRepository = $settingsRepository;
	}

	/**
	 * Import the properties.
	 *
	 * @param array $properties
	 * @param bool  $force
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
			$languages = ['en', 'de', 'it', 'es', 'pt', 'fr', 'nl']; // the order here is very important for calculating the hash key

			foreach($properties as $propertyKey)
			{
				try
				{
					$enum = [];

					foreach($languages as $language)
					{
						if($propertyKey == 'when_made')
						{
							$enum[ $language ] = $this->dataTypeService->describeWhenMadeEnum($language);
						}
						elseif($propertyKey == 'who_made')
						{
							$enum[ $language ] = $this->dataTypeService->describeWhoMadeEnum($language);
						}
						elseif($propertyKey == 'occasion')
						{
							$enum[ $language ] = $this->dataTypeService->describeOccasionEnum($language);
						}
						elseif($propertyKey == 'recipient')
						{
							$enum[ $language ] = $this->dataTypeService->describeRecipientEnum($language);
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

		if(count($propertySettings))
		{
			foreach($propertySettings as $propertySetting)
			{
				$this->currentProperties[ $propertySetting->settings['mainPropertyKey'] ][ $propertySetting->settings['hash'] ] = $propertySetting;
			}
		}
	}

	/**
	 * Create settings for a given property key and enums.
	 *
	 * @param string $propertyKey
	 * @param array  $propertyEnum
	 */
	private function createSettings($propertyKey, $propertyEnum)
	{
		$defaultPropertyEnum = reset($propertyEnum);

		if(isset($defaultPropertyEnum['values']) && is_array($defaultPropertyEnum['values']) && count($defaultPropertyEnum['values']))
		{
			foreach($defaultPropertyEnum['values'] as $key => $propertyValueKey)
			{
				$data = [
					'mainPropertyKey' => $propertyKey,
					'hash'            => $this->calculateHash($propertyKey, $propertyValueKey),
				];

				foreach(array_keys($propertyEnum) as $lang)
				{
					$data['propertyKey'][ $lang ]      = $propertyKey;
					$data['propertyValueKey'][ $lang ] = $propertyEnum[ $lang ]['values'][ $key ];

					$data['propertyName'][ $lang ] = $this->getPropertyName($propertyKey, $lang);

					if(isset($propertyEnum[ $lang ]['formatted']) && is_array($propertyEnum[ $lang ]['formatted']) && isset($propertyEnum[ $lang ]['formatted'][ $propertyEnum[ $lang ]['values'][ $key ] ]) && strlen($propertyEnum[ $lang ]['formatted'][ $propertyEnum[ $lang ]['values'][ $key ] ]))
					{
						$data['propertyValueName'][ $lang ] = $propertyEnum[ $lang ]['formatted'][ $propertyEnum[ $lang ]['values'][ $key ] ];
					}
					else
					{
						$data['propertyValueName'][ $lang ] = $this->formatName($propertyEnum[ $lang ]['values'][ $key ]);
					}
				}

				$currentProperty = $this->currentProperty($data);

				if(!$currentProperty)
				{
					$this->settingsRepository->create('EtsyIntegrationPlugin', SettingsCorrelationFactory::TYPE_PROPERTY, $data);
				}
				else
				{
					$this->settingsRepository->update($data, $currentProperty->id);
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

		if(isset($map[ $propertyKey ]) && isset($map[ $propertyKey ][ $lang ]) && strlen($map[ $propertyKey ][ $lang ]))
		{
			return $map[ $propertyKey ][ $lang ];
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
	 * @param array $data
	 *
	 * @return Settings|null
	 */
	private function currentProperty($data)
	{
		if(isset($this->currentProperties[ $data['mainPropertyKey'] ]) && isset($this->currentProperties[ $data['mainPropertyKey'] ][ $data['hash'] ]))
		{
			return $this->currentProperties[ $data['mainPropertyKey'] ][ $data['hash'] ];
		}

		return null;
	}

	/**
	 * Calculate hash value for array.
	 *
	 * @param string $propertyKey
	 * @param string $propertyValueKey
	 *
	 * @return string
	 */
	private function calculateHash($propertyKey, $propertyValueKey)
	{
		return md5(json_encode([
			                       'propertyKey'      => $propertyKey,
			                       'propertyValueKey' => $propertyValueKey
		                       ]));
	}
}
