<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

/**
 * Class TaxonomyService
 */
class TaxonomyService
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
	 * @param string $language
	 * @return array
	 */
	public function getSellerTaxonomy($language)
	{
		$response = $this->client->call('getSellerTaxonomy', [
			'language' => $language,
		]);

		$results = $response['results'];

		if(is_array($results))
		{
			return $results;
		}

		return [];
	}
}
