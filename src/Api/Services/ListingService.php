<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\EtsyServiceProvider;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ListingService
 */
class ListingService
{
    use Loggable;

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
     * Performs a getListing call to Etsy.
     *
     * @param $listingId
     * @param $language
     * @return array
     */
	public function getListing($listingId)
    {
        return $this->client->call(
            'getListing',
            [
                'listing_id' => $listingId
            ],
            [],
            [],
            [
                'Translations' => 'Translations'
            ]
        );
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
        $this->getLogger(EtsyServiceProvider::START_LISTING_SERVICE)
            ->info(EtsyServiceProvider::PLUGIN_NAME, [
                'function' => 'createListing',
                'data' => $data
            ]);
		return $this->client->call('createListing', ['language' => $language], $data);
	}

    /**
     * Performs an updateListing call to Etsy.
     *
     * @param int $id
     * @param array $data
     * @param string $language
     * @return array
     */
	public function updateListing($id, $data, $language = '')
	{
		$params['listing_id'] = $id;

		if(strlen($language) > 0)
		{
			$params['language'] = $language;
		}

        $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_SERVICE)
            ->addReference('etsyListingId',$id)
            ->info(EtsyServiceProvider::PLUGIN_NAME, [
                'params' => $params,
                'data' => $data
            ]);

		$response = $this->client->call('updateListing', $params, $data);

        $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_SERVICE)
            ->addReference('etsyListingId',$id)
            ->info(EtsyServiceProvider::PLUGIN_NAME, [
                'action' => 'update',
                'params' => $params,
                'response' => $response
            ]);

        return $response;
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
		$response = $this->client->call('deleteListing', ['listing_id' => $id]);


        $this->getLogger(EtsyServiceProvider::UPDATE_LISTING_SERVICE)
            ->addReference('etsyListingId',$id)
            ->info(EtsyServiceProvider::PLUGIN_NAME, [
                'action' => 'delete',
                'id' => $id,
                'response' => $response
            ]);

		return true;
	}
}
