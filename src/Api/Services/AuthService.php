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
	 *
	 * @return array
	 */
	public function getRequestToken($callback)
	{
		$response = $this->client->call('getRequestToken', [
			'callback' => $callback,
			'scope'    => 'email_r listings_r listings_w listings_d transactions_r transactions_w billing_r profile_r profile_w address_r address_w favorites_rw shops_rw cart_rw recommend_rw feedback_r treasury_r treasury_w',
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
