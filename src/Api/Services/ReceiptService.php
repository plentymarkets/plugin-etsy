<?php

namespace Etsy\Api\Services;

use Etsy\Logger\Logger;
use Etsy\Api\Client;

/**
 * Class ReceiptService
 */
class ReceiptService
{
	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var Logger
	 */
	private $logger;

	public function __construct(Client $client, Logger $logger)
	{
		$this->client = $client;
		$this->logger = $logger;
	}

	/**
	 * @param int    $shopId
	 * @param string $from
	 * @param string $to
	 * @return array
	 */
	public function findAllShopReceipts($shopId, $from, $to)
	{
		$response = $this->client->call('findAllShopReceipts', [
			                                                     'shop_id'     => $shopId,
			                                                     'min_created' => $from,
			                                                     'max_created' => $to,
		                                                     ], [], [], [
			                                'Transactions' => 'Transactions',
			                                'Buyer'        => 'Buyer',
		                                ], true);

		return $response['results'];
	}
}
