<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\EtsyServiceProvider;
use Etsy\Helper\SettingsHelper;
use Plenty\Plugin\Log\Loggable;

//use OAuth;

/**
 * Class ListingInventoryService
 */
class ListingInventoryService
{
    use Loggable;

    const CUSTOM_ATTRIBUTE_1 = 513;
    const CUSTOM_ATTRIBUTE_2 = 514;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @param Client $client
     */
    public function __construct(Client $client, SettingsHelper $settingsHelper)
    {
        $this->client = $client;
        $this->settingsHelper = $settingsHelper;
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
        $params['listing_id'] = $listingId;

        if (strlen($language) > 0) {
            $params['language'] = $language;
        }

        if (isset($data['price_on_property']))
        {
            $params['price_on_property'] = implode(',', $data['price_on_property']);
            unset($data['price_on_property']);
        }

        if (isset($data['quantity_on_property']))
        {
            $params['quantity_on_property'] = implode(',',$data['quantity_on_property']);
            unset($data['quantity_on_property']);
        }

        if (isset($data['sku_on_property']))
        {
            $params['sku_on_property'] = implode(',',$data['sku_on_property']);
            unset($data['sku_on_property']);
        }

        return $this->client->call('updateInventory', $params, $data);

    }
}
