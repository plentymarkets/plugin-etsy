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
     * @param int $listingId
     * @param string $image
     * @param $position
     *
     * @return mixed
     * @throws \Exception
     */
	public function uploadListingImage($listingId, $image, $position)
	{
		$data = [
			'image' => $image,
            'rank' => $position
		];

		return $this->client->call('uploadListingImage', [
			'listing_id' => $listingId,
		], $data);
	}

    /**
     * @param $listingId
     * @param $imageId
     * @return array
     * @throws \Exception
     */
    public function deleteListingImage($listingId, $imageId)
    {
        return $this->client->call('deleteListingImage', [
            'listing_id' => $listingId,
            'listing_image_id' => $imageId
        ]);
	}
}
