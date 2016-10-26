<?php

namespace Etsy\Api\Services;

use Etsy\Logger\Logger;
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
		                                ], true);

		if(is_null($response) || (array_key_exists('exception', $response) && $response['exception'] === true))
		{
			$this->logger->log('Could not get shipping template id "' . $id . '" for language "' . $language . '". Reason: ...');

			return []; // TODO  throw exception
		}

		$results = $response['results'];

		if(is_array($results))
		{
			return reset($results);
		}

		return [];
	}

	/**
	 * @param int    $userId
	 * @param string $language
	 * @return array
	 */
	public function findAllUserShippingProfiles($userId, $language):array
	{
		/*
		$response = $this->client->call('findAllUserShippingProfiles', [
			'language' => $language,
			'user_id' => $userId,
		],
		[],
		[],
		[
			'Entries' => 'Entries',
			'Upgrades' => 'Upgrades',
		], true);
		*/
		$response = $this->mockup();

		if(is_null($response) || (array_key_exists('exception', $response) && $response['exception'] === true))
		{
			$this->logger->log('Could not get shipping profiles for user id "' . $userId . '" and language "' . $language . '". Reason: ...');

			return []; // TODO  throw exception
		}

		$results = $response['results'];

		if(is_array($results))
		{
			return $results;
		}

		return [];
	}

	/**
	 * @return array
	 */
	private function mockup()
	{
		return array(
			'count'      => 3,
			'results'    => array(
				0 => array(
					'shipping_template_id'          => 27861830444,
					'title'                         => 'DE 0,1 USA 0,2 Sonst 0,3 1-2 weeks',
					'user_id'                       => 97266715,
					'min_processing_days'           => 5,
					'max_processing_days'           => 10,
					'processing_days_display_label' => '1-2 Wochen',
					'origin_country_id'             => 91,
					'Entries'                       => array(
						0 => array(
							'shipping_template_entry_id' => 13251007530,
							'shipping_template_id'       => 27861830444,
							'currency_code'              => 'EUR',
							'origin_country_id'          => 91,
							'destination_country_id'     => null,
							'destination_region_id'      => null,
							'primary_cost'               => '0.30',
							'secondary_cost'             => '0.30',
						),
						1 => array(
							'shipping_template_entry_id' => 13244682427,
							'shipping_template_id'       => 27861830444,
							'currency_code'              => 'EUR',
							'origin_country_id'          => 91,
							'destination_country_id'     => 91,
							'destination_region_id'      => null,
							'primary_cost'               => '0.10',
							'secondary_cost'             => '0.10',
						),
						2 => array(
							'shipping_template_entry_id' => 13251007532,
							'shipping_template_id'       => 27861830444,
							'currency_code'              => 'EUR',
							'origin_country_id'          => 91,
							'destination_country_id'     => 209,
							'destination_region_id'      => null,
							'primary_cost'               => '0.20',
							'secondary_cost'             => '0.20',
						),
					),
					'Upgrades'                      => array(
						0 => array(
							'shipping_profile_id' => 27861830444,
							'value_id'            => 49967493374,
							'value'               => 'Mein Upgrade',
							'price'               => 0.2,
							'secondary_price'     => 0.1,
							'currency_code'       => 'EUR',
							'type'                => 0,
							'order'               => 0,
							'language'            => 0,
						),
					),
				),
				1 => array(
					'shipping_template_id'          => 27860792802,
					'title'                         => 'Gratis Deutschland, 10 USA, 1 Sonst',
					'user_id'                       => 97266715,
					'min_processing_days'           => 3,
					'max_processing_days'           => 5,
					'processing_days_display_label' => '3-5 Werktagen',
					'origin_country_id'             => 91,
					'Entries'                       => array(
						0 => array(
							'shipping_template_entry_id' => 13244654917,
							'shipping_template_id'       => 27860792802,
							'currency_code'              => 'EUR',
							'origin_country_id'          => 91,
							'destination_country_id'     => null,
							'destination_region_id'      => null,
							'primary_cost'               => '1.00',
							'secondary_cost'             => '1.00',
						),
						1 => array(
							'shipping_template_entry_id' => 13250979840,
							'shipping_template_id'       => 27860792802,
							'currency_code'              => 'EUR',
							'origin_country_id'          => 91,
							'destination_country_id'     => 91,
							'destination_region_id'      => null,
							'primary_cost'               => '0.00',
							'secondary_cost'             => '0.00',
						),
						2 => array(
							'shipping_template_entry_id' => 13244654919,
							'shipping_template_id'       => 27860792802,
							'currency_code'              => 'EUR',
							'origin_country_id'          => 91,
							'destination_country_id'     => 209,
							'destination_region_id'      => null,
							'primary_cost'               => '10.00',
							'secondary_cost'             => '10.00',
						),
					),
					'Upgrades'                      => array(
						0 => array(
							'shipping_profile_id' => 27860792802,
							'value_id'            => 49967493374,
							'value'               => 'Mein Upgrade',
							'price'               => 0.2,
							'secondary_price'     => 0.1,
							'currency_code'       => 'EUR',
							'type'                => 0,
							'order'               => 0,
							'language'            => 0,
						),
					),
				),
				2 => array(
					'shipping_template_id'          => 28734983909,
					'title'                         => 'Gratisversand',
					'user_id'                       => 97266715,
					'min_processing_days'           => 1,
					'max_processing_days'           => 1,
					'processing_days_display_label' => '1 Werktag',
					'origin_country_id'             => 91,
					'Entries'                       => array(
						0 => array(
							'shipping_template_entry_id' => 13226938786,
							'shipping_template_id'       => 28734983909,
							'currency_code'              => 'EUR',
							'origin_country_id'          => 91,
							'destination_country_id'     => null,
							'destination_region_id'      => null,
							'primary_cost'               => '0.00',
							'secondary_cost'             => '0.00',
						),
						1 => array(
							'shipping_template_entry_id' => 13220834599,
							'shipping_template_id'       => 28734983909,
							'currency_code'              => 'EUR',
							'origin_country_id'          => 91,
							'destination_country_id'     => 91,
							'destination_region_id'      => null,
							'primary_cost'               => '0.00',
							'secondary_cost'             => '0.00',
						),
					),
					'Upgrades'                      => array(
						0 => array(
							'shipping_profile_id' => 28734983909,
							'value_id'            => 49967493374,
							'value'               => 'Mein Upgrade',
							'price'               => 0.2,
							'secondary_price'     => 0.1,
							'currency_code'       => 'EUR',
							'type'                => 0,
							'order'               => 0,
							'language'            => 0,
						),
					),
				),
			),
			'params'     => array(
				'user_id' => '__SELF__',
				'limit'   => 25,
				'offset'  => 0,
				'page'    => null,
			),
			'type'       => 'ShippingTemplate',
			'pagination' => array(
				'effective_limit'  => 25,
				'effective_offset' => 0,
				'next_offset'      => null,
				'effective_page'   => 1,
				'next_page'        => null,
			),
		);
	}
}
