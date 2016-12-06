<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\Logger\Logger;

/**
 * Class ListingService
 */
class ListingService
{
	const STATE_DRAFT = 'draft';
	const STATE_ACTIVE = 'active';
	const STATE_INACTIVE = 'inactive';

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
	 * Performs an createListing call to Etsy.
	 *
	 * @param string $language
	 * @param array  $data
	 *
	 * @return int
	 */
	public function createListing($language, array $data)
	{
		$response = $this->client->call('createListing', ['language' => $language], $data);

		return (int) reset($response['results'])['listing_id'];
	}

	/**
	 * Performs an updateListing call to Etsy.
	 *
	 * @param int   $id
	 * @param array $data
	 *
	 * @return bool
	 */
	public function updateListing($id, $data)
	{
		$this->client->call('updateListing', [
			'listing_id' => $id,
		], $data);

		return true;
	}

	/**
	 * Performs an deleteListing call to Etsy.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function deleteListing($id)
	{
		$this->client->call('deleteListing', [
			'listing_id' => $id
		]);

		return true;
	}
}
