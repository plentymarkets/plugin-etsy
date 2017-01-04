<?php
namespace Etsy\Migrations;

use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;
use Plenty\Plugin\ConfigRepository;

use Etsy\Helper\ImageHelper;

/**
 * Class CreateVariationImageTable
 */
class CreateVariationImageTable
{
	/**
	 * @param DynamoDbRepositoryContract $dynamoDbRepository
	 * @param ConfigRepository $config
	 */
	public function run(DynamoDbRepositoryContract $dynamoDbRepository, ConfigRepository $config)
	{
		$dynamoDbRepository->createTable(SettingsHelper::PLUGIN_NAME, ImageHelper::TABLE_NAME, [
			[
				'AttributeName' => 'id',
				'AttributeType' => DynamoDbRepositoryContract::FIELD_TYPE_STRING
			],
		], [
			                                 [
				                                 'AttributeName' => 'id',
				                                 'KeyType'       => 'HASH',
			                                 ],
		                                 ], (int) $config->get(SettingsHelper::PLUGIN_NAME . '.readCapacityUnits', 3), (int) $config->get(SettingsHelper::PLUGIN_NAME . '.readCapacityUnits', 2));
	}
}