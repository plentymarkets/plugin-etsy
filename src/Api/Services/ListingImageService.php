<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\EtsyServiceProvider;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ListingImageService
 */
class ListingImageService
{
    use Loggable;
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
     * @param $listingId
     * @param $image
     * @param $position
     * @return array
     * @throws \Exception
     */
	public function uploadListingImage($listingId, $image, $position)
	{
		$data = [
			'image' => $image,
            'rank' => $position
		];

		$response = $this->client->call('uploadListingImage', [
			'listing_id' => $listingId,
		], $data);

		return $response;
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
