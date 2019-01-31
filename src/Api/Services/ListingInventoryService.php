<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\Helper\SettingsHelper;
use OAuth;

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
        if (strlen($language) > 0) {
            $params['language'] = $language;
        }

        $params['listing_id'] = $listingId;

        $oAuthAuthentication = json_decode($this->settingsHelper->get(SettingsHelper::SETTINGS_ACCESS_TOKEN), true);

        $oauth = new OAuth(
            $oAuthAuthentication['consumerKey'],
            $oAuthAuthentication['consumerSecret'],
            OAUTH_SIG_METHOD_HMACSHA1,
            OAUTH_AUTH_TYPE_URI
        );
        $oauth->setToken($oAuthAuthentication['accessToken'], $oAuthAuthentication['accessTokenSecret']);

        $etsy_base = 'https://openapi.etsy.com/v2';

        $propertyIds = implode(',', $data['price_on_property']);

        $inventory_uri = sprintf(
            '%s/listings/%d/inventory?price_on_property='.$propertyIds.'&quantity_on_property='.$propertyIds,
            $etsy_base,
            $listingId
        );

        $data = $oauth->fetch(
            $inventory_uri,
            ['products' => $data['products']],
            OAUTH_HTTP_METHOD_PUT
        );

        $test = true;



    }
}
