<?php

namespace Etsy\Helper;

use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Market\Settings\Models\Settings;
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
	 * @param string $id
	 * @param string $value
	 *
	 * @return bool
	 */
	public function save($id, $value)
	{
		return $this->dynamoDbRepo->putItem(SettingsHelper::PLUGIN_NAME, self::TABLE_NAME, [
			'id'    => [
				DynamoDbRepositoryContract::FIELD_TYPE_STRING => (string) $id,
			],
			'value' => [
				DynamoDbRepositoryContract::FIELD_TYPE_STRING => (string) $value,
			],
		]);
	}

	/**
	 * Get image data for a given id.
	 *
	 * @param string $id
	 * @param mixed $default
	 *
	 * @return string|null
	 */
	public function get($id, $default = null)
	{
		$data = $this->dynamoDbRepo->getItem(SettingsHelper::PLUGIN_NAME, self::TABLE_NAME, true, [
			'id' => [
				DynamoDbRepositoryContract::FIELD_TYPE_STRING => $id
			]
		]);

		if(isset($data['value'][DynamoDbRepositoryContract::FIELD_TYPE_STRING]))
		{
			return $data['value'][DynamoDbRepositoryContract::FIELD_TYPE_STRING];
		}

		return $default;
	}

    public function update($id, $value)
    {
        return $this->dynamoDbRepo->putItem(SettingsHelper::PLUGIN_NAME, self::TABLE_NAME, [
            'id' => [
                DynamoDbRepositoryContract::FIELD_TYPE_STRING => $id
            ],
            'value' => [
                DynamoDbRepositoryContract::FIELD_TYPE_STRING => $value
            ]
        ]);
	}

    /**
     * Sort the image array to make it look like the plenty image positions and iterate every position
     * because Etsy image positions start at 1
     *
     * @param $imageList
     * @return mixed
     */
    public function sortImagePosition($imageList)
    {
        $position = [];

        foreach ($imageList as $key => $row) {
            $position[$key]  = $row['position'];
        }

        array_multisort($position, SORT_ASC, $imageList);

        $counter = 1;

        foreach ($imageList as $key => $imagePosition) {

            $imageList[$key]['position'] = $counter;

            $counter++;
        }

        return $imageList;
	}
}
