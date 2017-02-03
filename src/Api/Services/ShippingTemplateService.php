<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

/**
 * Class ShippingTemplateService
 */
class ShippingTemplateService
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
	 * @param int    $id
	 * @param string $language
	 * @return array
	 */
	public function getShippingTemplate($id, $language):array
	{
		$response = $this->client->call('getShippingTemplate', [
			'language'             => $language,
			'shipping_template_id' => $id,
		], [], [], [
            'Entries'  => 'Entries',
            'Upgrades' => 'Upgrades',
        ]);

		$results = $response['results'];

		if(is_array($results))
		{
			return reset($results);
		}

		return [];
	}

	/**
	 * @param int|string    $userId
	 * @param string $language
	 * @return array
	 */
	public function findAllUserShippingProfiles($userId, $language):array
	{
		$response = $this->client->call('findAllUserShippingProfiles', [
			'language' => $language,
			'user_id'  => $userId,
		], [], [], [
            'Entries'  => 'Entries',
            'Upgrades' => 'Upgrades',
        ]);

		$results = $response['results'];

		if(is_array($results))
		{
			return $results;
		}

		return [];
	}
}
