<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\Logger\Logger;

/**
 * Class ListingImageService
 */
class ListingImageService
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
	 * @param int    $listingId
	 * @param string $image
	 */
	public function uploadListingImage($listingId, $image)
	{
		$data = [
			'image' => $image,
		];

		$this->client->call('uploadListingImage', [
			'listing_id' => $listingId,
		], $data);
	}
}
