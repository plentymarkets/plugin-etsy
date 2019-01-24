<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

/**
 * Class ListingInventoryService
 */
class ListingInventoryService
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

    //todo: Inventory calls implementieren
}
