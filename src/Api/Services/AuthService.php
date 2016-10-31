<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\Logger\Logger;

/**
 * Class AuthService
 */
class AuthService
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
	 * @param string $callback
	 * @return array
	 */
	public function getRequestToken($callback)
	{
		$response = $this->client->call('getRequestToken', [
			'callback' => $callback,
		]);

		return $response;
	}

	public function getAccessToken($oauthToken, $oauthTokenSecret, $verifier)
	{
		$response = $this->client->call('getAccessToken', [
			'oauthToken'       => $oauthToken,
			'oauthTokenSecret' => $oauthTokenSecret,
			'verifier'         => $verifier,
		]);

		return $response;
	}
}
