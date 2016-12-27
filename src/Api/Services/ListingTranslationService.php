<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Plenty\Modules\Item\DataLayer\Models\ItemDescription;

/**
 * Class ListingTranslationService
 */
class ListingTranslationService
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
	 * Creates a ListingTranslation by listing_id and language
	 *
	 * @param int    $listingId
	 * @param string $language
	 * @param array  $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function createListingTranslation($listingId, $language, array $data)
	{
		return $this->client->call('createListingTranslation', [
			'listing_id' => $listingId,
			'language'   => $language,
		], $data);
	}

	/**
	 * Updates a ListingTranslation by listing_id and language
	 *
	 * @param int    $listingId
	 * @param string $language
	 * @param array  $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function updateListingTranslation($listingId, $language, array $data)
	{
		return $this->client->call('updateListingTranslation', [
			'listing_id' => $listingId,
			'language'   => $language,
		], $data);
	}
}
