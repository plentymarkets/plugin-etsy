<?php

use Etsy\EtsyClient;
use Etsy\EtsyRequestException;

/**
 * Class AuthClient
 */
class AuthClient
{
	private $client;

	public function __construct($consumerKey, $consumerSecret)
	{
		$this->client = new EtsyClient($consumerKey, $consumerSecret);
	}

	/**
	 * Get the request token.
	 *
	 * @param array $extra
	 * @return array
	 */
	public function getRequestToken(array $extra = array())
	{
		try
		{
			$data = $this->client->getRequestToken($extra);

			if(is_null($data) || (isset($data['error']) && $data['error'] == true))
			{
				throw new \Exception($data['error_msg']);
			}

			return $data;
		}
		catch(\Exception $ex)
		{
			return [
				'exception' => true,
				'message' => $ex->getMessage(),
			];
		}

	}

	/**
	 * Get the access token.
	 *
	 * @param array $params
	 * @return array
	 */
	public function getAccessToken(array $params = array())
	{
		try
		{
			$this->client->authorize($params['oauthToken'], $params['oauthTokenSecret']);

			$data = $this->client->getAccessToken($params['verifier']);

			if(is_null($data) || (isset($data['error']) && $data['error'] == true))
			{
				throw new \Exception($data['error_msg']);
			}

			return [
				'consumerKey'       => $this->client->getConsumerKey(),
				'consumerSecret'    => $this->client->getConsumerSecret(),
				'tokenSecret'       => $params['oauthToken'],
				'token'             => $params['oauthTokenSecret'],
				'accessToken'       => $data['oauth_token'],
				'accessTokenSecret' => $data['oauth_token_secret'],
			];
		}
		catch(EtsyRequestException $ex)
		{
			return [
				'exception' => true,
				'message' => $ex->getLastResponse(),
			];
		}
		catch(\Exception $ex)
		{
			return [
				'exception' => true,
				'message' => $ex->getMessage(),
			];
		}
	}
}