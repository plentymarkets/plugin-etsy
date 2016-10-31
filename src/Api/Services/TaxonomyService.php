<?php

namespace Etsy\Api\Services;

use Etsy\Logger\Logger;
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
