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

		if(is_null($response) || (array_key_exists('exception', $response) && $response['exception'] === true))
		{
			$this->logger->log('Could not get seller taxonomies for language "' . $language . '". Reason: ...');

			return []; // TODO  throw exception
		}

		$results = $response['results'];

		if(is_array($results))
		{
			return $results;
		}

		return [];
	}
}
