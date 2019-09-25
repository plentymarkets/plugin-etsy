<?php


namespace Etsy\Migrations;


use Etsy\Helper\SettingsHelper;
use Etsy\Wizards\MigrationAssistant;
use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;
use Plenty\Plugin\ConfigRepository;

class CreateEtsyMigrationTable
{
    public function run(DynamoDbRepositoryContract $dynamoDbRepository, ConfigRepository $config)
    {
        $dynamoDbRepository->createTable(SettingsHelper::PLUGIN_NAME, MigrationAssistant::TABLE_NAME, [
            [
                'AttributeName' => 'name',
                'AttributeType' => DynamoDbRepositoryContract::FIELD_TYPE_BOOL
            ],
        ], [
            [
                'AttributeName' => 'name',
                'KeyType' => 'HASH',
            ],
        ], (int)$config->get(SettingsHelper::PLUGIN_NAME . '.readCapacityUnits', 3),
            (int)$config->get(SettingsHelper::PLUGIN_NAME . '.writeCapacityUnits', 2));

        $dynamoDbRepository->putItem(SettingsHelper::PLUGIN_NAME, MigrationAssistant::TABLE_NAME, [
            'name'  => [
                DynamoDbRepositoryContract::FIELD_TYPE_STRING => (string) "isRun",
            ],
            'value' => [
                DynamoDbRepositoryContract::FIELD_TYPE_BOOL => (bool) false,
            ],
        ]);
    }

}