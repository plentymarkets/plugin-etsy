<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

/**
 * Class ListingInventoryService
 */
class ListingInventoryService
{
    const CUSTOM_ATTRIBUTE_1 = 513;
    const CUSTOM_ATTRIBUTE_2 = 514;

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
     * @param string $language
     * @return array
     */
    public function getInventory(int $listingId, string $language = '')
    {
        if (strlen($language) > 0) {
            $params['language'] = $language;
        }

        $params['listing_id'] = $listingId;

        return $this->client->call('getInventory', $params);
    }

    /**
     * @param int $listingId
     * @param array $data
     * @param string $language
     * @return array
     */
    public function updateInventory(int $listingId, array $data, string $language = '')
    {
        if (strlen($language) > 0) {
            $params['language'] = $language;
        }

        $params['listing_id'] = $listingId;

        return $this->client->call('updateInventory', $params, $data);
    }
}
