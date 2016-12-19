<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

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
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * @param int    $listingId
	 * @param string $image
	 *
	 * @return mixed
	 */
	public function uploadListingImage($listingId, $image)
	{
		$data = [
			'image' => $image,
		];

		return $this->client->call('uploadListingImage', [
			'listing_id' => $listingId,
		], $data);
	}
}
