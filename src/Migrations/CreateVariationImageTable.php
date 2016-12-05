<?php
namespace Etsy\Migrations;

use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;
use Plenty\Plugin\ConfigRepository;

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
		$dynamoDbRepository->createTable('EtsyIntegrationPlugin', 'variation_images', [
			[
				'AttributeName' => 'id',
				'AttributeType' => DynamoDbRepositoryContract::FIELD_TYPE_STRING
			],
		], [
			                                 [
				                                 'AttributeName' => 'id',
				                                 'KeyType'       => 'HASH',
			                                 ],
		                                 ], (int) $config->get('EtsyIntegrationPlugin.readCapacityUnits', 3), (int) $config->get('EtsyIntegrationPlugin.readCapacityUnits', 2));
	}
}