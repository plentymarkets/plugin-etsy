<?php
namespace Etsy\Migrations;

use Etsy\Models\Settings;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;

/**
 * Class CreateSettingsTable
 */
class CreateSettingsTable
{
	/**
	 * @param DynamoDbRepositoryContract $dynamoDbRepository
	 */
	public function run(DynamoDbRepositoryContract $dynamoDbRepository, Migrate $migrate)
	{
		$migrate->createTable(Settings::class, 10, 20); // TODO use config (use default 3,3) // use DynamoDBRepo
	}
}