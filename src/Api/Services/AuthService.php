<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

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
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
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
