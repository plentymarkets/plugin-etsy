<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

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
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Performs an createListing call to Etsy.
	 *
	 * @param string $language
	 * @param array  $data
	 *
	 * @return array
	 */
	public function createListing($language, array $data)
	{
		return $this->client->call('createListing', ['language' => $language], $data);
	}

	/**
	 * Performs an updateListing call to Etsy.
	 *
	 * @param int    $id
	 * @param array  $data
	 * @param string $language
	 *
	 * @return bool
	 */
	public function updateListing($id, $data, $language = '')
	{
		$params['listing_id'] = $id;

		if(strlen($language) > 0)
		{
			$params['language'] = $language;
		}

		$this->client->call('updateListing', $params, $data);

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
		$this->client->call('deleteListing', ['listing_id' => $id]);

		return true;
	}
}
