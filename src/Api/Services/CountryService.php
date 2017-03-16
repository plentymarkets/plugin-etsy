<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

/**
 * Class CountryService
 */
class CountryService
{
	/**
	 * @var Client
	 */
	private $client;

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Retrieves a set of Receipt objects associated to a Shop.
	 *
	 * @return array
	 */
	public function findAllCountry()
	{
		return $this->client->call('findAllCountry');
	}
}
