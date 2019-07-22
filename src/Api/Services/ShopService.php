<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

/**
 * Class ShopService
 */
class ShopService
{
	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Retrieves a set of Shop objects associated to a User.
	 *
	 * @param int|string $userId
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function findAllUserShops($userId):array
	{
		$response = $this->client->call('findAllUserShops', [
			'user_id' => $userId,
		]);

		return $response;
	}

    public function findAllShopSections($shopId):array
    {
        $response = $this->client->call('findAllShopSections', [
            'shop_id' => $shopId
        ]);

        return $response;
	}
}
