<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\Logger\Logger;

/**
 * Class StyleService
 */
class StyleService
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
	 * Retrieve all suggested styles.
	 *
	 * @param string $lang
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function findSuggestedStyles($lang)
	{
		$response = $this->client->call('findSuggestedStyles', [
			'language' => $lang
		]);

		if(!isset($response['results']) || !is_array($response['results']) || count($response['results']) <= 0)
		{
			return [];
		}

		return $response['results'];
	}
}