<?php

namespace Etsy\Services\Property;

use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;

use Etsy\Api\Services\DataTypeService;
use Etsy\Api\Services\StyleService;
use Plenty\Modules\Market\Settings\Models\Settings;
use Plenty\Plugin\Log\Loggable;

/**
 * Class PropertyImportService
 */
class PropertyImportService
{
	use Loggable;

	/**
	 * @var DataTypeService
	 */
	private $dataTypeService;

	/**
	 * @var StyleService
	 */
	private $styleService;

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
	 * @param StyleService               $styleService
	 * @param SettingsRepositoryContract $settingsRepository
	 */
	public function __construct(
		DataTypeService $dataTypeService,
		StyleService $styleService,
		SettingsRepositoryContract $settingsRepository
	) {
		$this->dataTypeService    = $dataTypeService;
		$this->styleService       = $styleService;
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
		$newProperties = [];
		$oldProperties = $this->settingsRepository->find(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_PROPERTY);

		if ($force) {
			$this->settingsRepository->deleteAll(SettingsHelper::PLUGIN_NAME,
				SettingsCorrelationFactory::TYPE_PROPERTY);
		}

		$this->loadAllProperties();

		if (is_array($properties) && count($properties)) {
			$languages = [
				'en',
				'de',
				'it',
				'es',
				'pt',
				'fr',
				'nl'
			]; // the order here is very important for calculating the hash key

			foreach ($properties as $propertyKey) {
				try {
					$enum = [];

					foreach ($languages as $language) {
						switch ($propertyKey) {
							case 'when_made':
								$enum[$language] = $this->dataTypeService->describeWhenMadeEnum($language);
								break;

							case 'who_made':
								$enum[$language] = $this->dataTypeService->describeWhoMadeEnum($language);
								break;

							case 'occasion':
								$enum[$language] = $this->dataTypeService->describeOccasionEnum($language);
								break;

							case 'recipient':
								$enum[$language] = $this->dataTypeService->describeRecipientEnum($language);
								break;

							case 'style':
								$enum[$language] = $this->convertStyleTypeToEnumType($this->styleService->findSuggestedStyles($language));
								break;

							case 'is_supply':
								$enum[$language] = $this->convertIsSupplyTypeToEnumType($this->getIsSupplyTypes($language));
								break;
						}
					}

					if (is_array($enum) && count($enum)) {
						$createResultList = $this->createSettings($propertyKey, $enum, $language);

						$newProperties = array_merge($newProperties, $createResultList);
					}
				} catch (\Exception $ex) {
					$this->getLogger(__FUNCTION__)->error('Etsy::order.propertyImportError', $ex->getMessage());
				}
			}

			$propertyDifference = [];
			//$newProperties = $this->settingsRepository->find(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_PROPERTY);

			/** @var Settings $oldProperty */
			foreach($oldProperties as $oldProperty) {
				$propertyFound = false;

				/** @var Settings $newProperty */
				foreach($newProperties as $newProperty) {
					if($oldProperty->settings['hash'] == $newProperty->settings['hash']) {
						$propertyFound = true;
						break;
					}
				}

				if(!$propertyFound) {
					$propertyDifference[] = $oldProperty;
				}
			}

			$i = 0;

			/** @var Settings $property */
			foreach($propertyDifference as $property) {
				$this->settingsRepository->delete($property->id);
			}
		}
	}

	/**
	 * Load all properties that are currently imported.
	 */
	private function loadAllProperties()
	{
		$propertySettings = $this->settingsRepository->find(SettingsHelper::PLUGIN_NAME,
			SettingsCorrelationFactory::TYPE_PROPERTY);

		if (is_array($propertySettings) && count($propertySettings)) {
			foreach ($propertySettings as $propertySetting) {
				$this->currentProperties[$propertySetting->settings['mainPropertyKey']][$propertySetting->settings['hash']] = $propertySetting;
			}
		}
	}

	/**
	 * Convert the returned style type to an enum type.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function convertStyleTypeToEnumType($data): array
	{
		$enumType = [
			'type'      => 'enum',
			'values'    => [],
			'formatted' => [],
		];

		if (is_array($data) && count($data)) {
			foreach ($data as $result) {
				$enumType['values'][]                       = $result['style_id'];
				$enumType['formatted'][$result['style_id']] = $result['style'];

			}
		}

		return $enumType;
	}

	/**
	 * Convert the returned isSupply types to enum types.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function convertIsSupplyTypeToEnumType($data): array
	{
		$enumType = [
			'type'      => 'enum',
			'values'    => [],
			'formatted' => [],
		];

		if (is_array($data) && count($data)) {
			foreach ($data as $result) {
				$enumType['values'][]                    = $result['value'];
				$enumType['formatted'][$result['value']] = $result['name'];

			}
		}

		return $enumType;
	}

	/**
	 * Create settings for a given property key and enums.
	 *
	 * @param string $propertyKey
	 * @param array  $propertyEnum
	 * @param string $language
	 */
	private function createSettings(string $propertyKey, array $propertyEnum, string $language)
	{
		$settingsList = []; 
		$defaultPropertyEnum = reset($propertyEnum);

		if (isset($defaultPropertyEnum['values']) && is_array($defaultPropertyEnum['values']) && count($defaultPropertyEnum['values'])) {
			foreach ($defaultPropertyEnum['values'] as $key => $propertyValueKey) {
				$data = [
					'mainPropertyKey' => $propertyKey,
					'hash'            => $this->calculateHash($propertyKey, $propertyValueKey),
				];

				foreach (array_keys($propertyEnum) as $lang) {
					$data['propertyKey'][$lang]      = $propertyKey;
					$data['propertyValueKey'][$lang] = $propertyEnum[$lang]['values'][$key];

					$data['propertyName'][$lang] = $this->getPropertyName($propertyKey, $lang);

					if (isset($propertyEnum[$lang]['formatted']) && is_array($propertyEnum[$lang]['formatted']) && isset($propertyEnum[$lang]['formatted'][$propertyEnum[$lang]['values'][$key]]) && strlen($propertyEnum[$lang]['formatted'][$propertyEnum[$lang]['values'][$key]])) {
						$data['propertyValueName'][$lang] = $propertyEnum[$lang]['formatted'][$propertyEnum[$lang]['values'][$key]];
					} else {
						$data['propertyValueName'][$lang] = $this->formatName($propertyEnum[$lang]['values'][$key],
							$propertyKey, $lang);
					}
				}

				$currentProperty = $this->currentProperty($data);

				if (!$currentProperty) {
					$settingsList[] = $this->settingsRepository->create(SettingsHelper::PLUGIN_NAME,
						SettingsCorrelationFactory::TYPE_PROPERTY, $data);
				} else {
					$settingsList[] = $this->settingsRepository->update($data, $currentProperty->id);
				}
			}
		}
		
		return $settingsList;
	}

	/**
	 * Get isSupply types.
	 *
	 * @param string $lang
	 *
	 * @return array
	 */
	private function getIsSupplyTypes($lang)
	{
		switch ($lang) {
			case 'de':
				return [
					[
						'value' => 'true',
						'name'  => 'Zubehör oder ein Werkzeug, um etwas herzustellen'
					],

					[
						'value' => 'false',
						'name'  => 'Ein fertiges Produkt'
					],
				];

			case 'en':
			default:
				return [
					[
						'value' => 'true',
						'name'  => 'A supply or tool to make things'
					],

					[
						'value' => 'false',
						'name'  => 'A finished product'
					],
				];
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
			],
			'style'     => [
				'en' => 'Style',
				'de' => 'Stil',
			],
			'is_supply' => [
				'en' => 'What is it',
				'de' => 'Was ist es'

			],
		];

		if (isset($map[$propertyKey]) && isset($map[$propertyKey][$lang]) && strlen($map[$propertyKey][$lang])) {
			return $map[$propertyKey][$lang];
		}

		return $this->formatName($propertyKey, $propertyKey, $lang);
	}

	/**
	 * Format a name.
	 *
	 * @param string $name
	 * @param string $propertyKey
	 * @param string $lang
	 *
	 * @return string
	 */
	private function formatName($name, string $propertyKey, string $lang)
	{
		$name = ucwords(str_replace('_', ' ', $name));

		if ($propertyKey === 'occasion') {
			switch ($lang) {
				case 'de':
					$name = str_replace([
						'Jubilum',
					], [
						'Jubiläum',
					], $name);
					break;
			}
		} elseif ($propertyKey === 'recipient') {
			switch ($lang) {
				case 'de':
					$name = str_replace([
						'Mnner',
						'Teenager  Mdchen',
						'Mdchen',
						'Babys  Mdchen',
						'Vgel',
					], [
						'Männer',
						'Teenager Mädchen',
						'Mädchen',
						'Babys  Mädchen',
						'Vögel'
					], $name);
					break;
			}
		} elseif ($propertyKey === 'who_made') {
			switch ($lang) {
				case 'de':
					$name = str_replace([
						'I Did',
						'Collective',
						'Someone Else',
					], [
						'Ich war\'s',
						'Ein Mitglied meines Shops',
						'Eine andere Firma oder Person',
					], $name);
					break;
			}
		}


		return $name;
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
		if (isset($this->currentProperties[$data['mainPropertyKey']]) && isset($this->currentProperties[$data['mainPropertyKey']][$data['hash']])) {
			return $this->currentProperties[$data['mainPropertyKey']][$data['hash']];
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
