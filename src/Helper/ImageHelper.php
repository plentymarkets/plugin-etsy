<?php

namespace Etsy\Helper;

use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;

/**
 * Class ImageHelper
 */
class ImageHelper
{
	const TABLE_NAME = 'variation_images';

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
	 * Save image data to database.
	 *
	 * @param int    $id
	 * @param string $value
	 *
	 * @return bool
	 */
	public function save($id, $value)
	{
		return $this->dynamoDbRepo->putItem('EtsyIntegrationPlugin', self::TABLE_NAME, [
			'id'    => [
				DynamoDbRepositoryContract::FIELD_TYPE_NUMBER => (int) $id,
			],
			'value' => [
				DynamoDbRepositoryContract::FIELD_TYPE_STRING => (string) $value,
			],
		]);
	}

	/**
	 * Get image data for a given id.
	 *
	 * @param int   $id
	 * @param mixed $default
	 *
	 * @return string|null
	 */
	public function get($id, $default = null)
	{
		$data = $this->dynamoDbRepo->getItem('EtsyIntegrationPlugin', self::TABLE_NAME, true, [
			'id' => [
				DynamoDbRepositoryContract::FIELD_TYPE_NUMBER => $id
			]
		]);

		if(isset($data['value'][DynamoDbRepositoryContract::FIELD_TYPE_STRING]))
		{
			return $data['value'][DynamoDbRepositoryContract::FIELD_TYPE_STRING];
		}

		return $default;
	}
}
