<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

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
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
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