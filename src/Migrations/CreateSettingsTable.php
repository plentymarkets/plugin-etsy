<?php
namespace Etsy\Migrations;

use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;
use Plenty\Plugin\ConfigRepository;

/**
 * Class CreateSettingsTable
 */
class CreateSettingsTable
{
	/**
	 * @param DynamoDbRepositoryContract $dynamoDbRepository
	 * @param ConfigRepository $config
	 */
	public function run(DynamoDbRepositoryContract $dynamoDbRepository, ConfigRepository $config)
	{
		$dynamoDbRepository->createTable(SettingsHelper::PLUGIN_NAME, SettingsHelper::TABLE_NAME, [
			[
				'AttributeName' => 'name',
				'AttributeType' => DynamoDbRepositoryContract::FIELD_TYPE_STRING
			],
		], [
			                                 [
				                                 'AttributeName' => 'name',
				                                 'KeyType'       => 'HASH',
			                                 ],
		                                 ], (int) $config->get(SettingsHelper::PLUGIN_NAME . '.readCapacityUnits', 3), (int) $config->get(SettingsHelper::PLUGIN_NAME . '.readCapacityUnits', 2));
	}
}