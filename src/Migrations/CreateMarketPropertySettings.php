<?php
namespace Etsy\Migrations;

use Cache\Datatype\Set;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;

/**
 * Class CreateMarketPropertySettings
 */
class CreateMarketPropertySettings
{
	/**
	 * @param SettingsCorrelationFactory $settingsCorrelationFactory
	 */
	public function run(SettingsCorrelationFactory $settingsCorrelationFactory)
	{
		pluginApp(SettingsRepositoryContract::class)->deleteAll(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_PROPERTY);

		$settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)->clear(SettingsHelper::PLUGIN_NAME);

		$map = $this->getPropertyMap();

		foreach($map as $propertyName => $propertyValues)
		{
			foreach($propertyValues as $propertyValue)
			{
				pluginApp(SettingsRepositoryContract::class)->create(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_PROPERTY, [
					'propertyName' => $propertyName,
					'propertyValue' => $propertyValue,
				]);
			}
		}
	}

	/**
	 * Get the property map.
	 *
	 * @return array
	 */
	public function getPropertyMap()
	{
		$map = [
			'who_made' => [
				'i_did',
				'collective',
				'someone_else'
			],

			'when_made'            => [
				'made_to_order',
				'2010_2016',
				'2000_2009',
				'1997_1999',
				'before_1997',
				'1990_1996',
				'1980s',
				'1970s',
				'1960s',
				'1950s',
				'1940s',
				'1930s',
				'1920s',
				'1910s',
				'1900s',
				'1800s',
				'1700s',
				'before_1700'
			],
			'recipient'            => [
				'men',
				'women',
				'unisex_adults',
				'teen_boys',
				'teen_girls',
				'teens',
				'boys',
				'girls',
				'children',
				'baby_boys',
				'baby_girls',
				'babies',
				'birds',
				'cats',
				'dogs',
				'pets',
				'not_specified'
			],
			'occasion'             => [
				'anniversary',
				'baptism',
				'bar_or_bat_mitzvah',
				'birthday',
				'canada_day',
				'chinese_new_year',
				'cinco_de_mayo',
				'confirmation',
				'christmas',
				'day_of_the_dead',
				'easter',
				'eid',
				'engagement',
				'fathers_day',
				'get_well',
				'graduation',
				'halloween',
				'hanukkah',
				'housewarming',
				'kwanzaa',
				'prom',
				'july_4th',
				'mothers_day',
				'new_baby',
				'new_years',
				'quinceanera',
				'retirement',
				'st_patricks_day',
				'sweet_16',
				'sympathy',
				'thanksgiving',
				'valentines',
				'wedding'
			],
		];

		return $map;
	}
}