<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\Logger\Logger;
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
}
