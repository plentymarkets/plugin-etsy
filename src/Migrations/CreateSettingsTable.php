<?php
namespace Etsy\Migrations;

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
		$dynamoDbRepository->createTable('EtsyIntegrationPlugin', 'settings', [
			[
				'AttributeName' => 'name',
				'AttributeType' => DynamoDbRepositoryContract::FIELD_TYPE_STRING
			],
		], [
			                                 [
				                                 'AttributeName' => 'name',
				                                 'KeyType'       => 'HASH',
			                                 ],
		                                 ], (int) $config->get('EtsyIntegrationPlugin.readCapacityUnits', 3), (int) $config->get('EtsyIntegrationPlugin.readCapacityUnits', 2));
	}
}