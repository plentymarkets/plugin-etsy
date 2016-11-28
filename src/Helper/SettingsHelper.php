<?php

namespace Etsy\Helper;

use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;

/**
 * Class SettingsHelper
 */
class SettingsHelper
{
	const SETTINGS_TOKEN_REQUEST = 'token_request';
	const SETTINGS_ACCESS_TOKEN = 'access_token';
	const SETTINGS_SETTINGS = 'settings';
	const SETTINGS_ORDER_REFERRER = 'order_referrer';

	/**
	 * @var DynamoDbRepositoryContract
	 */
	private $dynamoDbRepo;

	/**
	 * @param DynamoDbRepositoryContract $dynamoDbRepository
	 */
	public function __construct(DynamoDbRepositoryContract $dynamoDbRepository)
	{
		$this->dynamoDbRepo = $dynamoDbRepository;
	}

	/**
	 * Save settings to database.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return bool
	 */
	public function save($name, $value)
	{
		return $this->dynamoDbRepo->putItem('EtsyIntegrationPlugin', 'settings', [
			'name'  => [
				'S' => (string) $name,
			],
			'value' => [
				'S' => (string) $value,
			],
		]);
	}

	/**
	 * Get settings for a given id.
	 *
	 * @param string $name
	 *
	 * @return string|null
	 */
	public function get($name)
	{
		$data = $this->dynamoDbRepo->getItem('EtsyIntegrationPlugin', 'settings', true, [
			'name' => ['S' => $name]
		]);

		if(isset($data['value']['S']))
		{
			return $data['value']['S'];
		}

		return null;
	}
}
