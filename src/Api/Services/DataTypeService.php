<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

/**
 * Class DataTypeService
 */
class DataTypeService
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
	 * Describes the legal values for Listing.occasion.
	 *
	 * @param string $lang
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function describeOccasionEnum($lang)
	{
		$response = $this->client->call('describeOccasionEnum', [
			'language' => $lang
		]);

		if(!isset($response['results']) || !is_array($response['results']) || count($response['results']) <= 0)
		{
			return [];
		}

		return $response['results'][0];
	}

	/**
	 * Describes the legal values for Listing.recipient.
	 *
	 * @param string $lang
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function describeRecipientEnum($lang)
	{
		$response = $this->client->call('describeRecipientEnum', [
			'language' => $lang
		]);

		if(!isset($response['results']) || !is_array($response['results']) || count($response['results']) <= 0)
		{
			return [];
		}

		return $response['results'][0];
	}

	/**
	 * Describes the legal values for Listing.when_made.
	 *
	 * @param string $lang
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function describeWhenMadeEnum($lang)
	{
		$response = $this->client->call('describeWhenMadeEnum', [
			'language'          => $lang,
		], [
			'include_formatted' => true,
		]);

		if(!isset($response['results']) || !is_array($response['results']) || count($response['results']) <= 0)
		{
			return [];
		}

		return $response['results'][0];
	}

	/**
	 * Describes the legal values for Listing.who_made.
	 *
	 * @param string $lang
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function describeWhoMadeEnum($lang)
	{
		$response = $this->client->call('describeWhoMadeEnum', [
			'language' => $lang
		]);

		if(!isset($response['results']) || !is_array($response['results']) || count($response['results']) <= 0)
		{
			return [];
		}

		return $response['results'][0];
	}
}
