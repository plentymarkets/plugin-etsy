<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\Logger\Logger;

/**
 * Class UserService
 */
class UserService
{
	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @param Client $client
	 * @param Logger $logger
	 */
	public function __construct(Client $client, Logger $logger)
	{
		$this->client = $client;
		$this->logger = $logger;
	}

	/**
	 * @param int|string $userId
	 * @return array
	 */
	public function getUser($userId)
	{
		$response = $this->client->call('getUser', [], [
			'user_id' => $userId
		]);

		return (int) reset($response['results'])['listing_id']; // TODO maybe it's better to return the entire listing data?
	}
}
