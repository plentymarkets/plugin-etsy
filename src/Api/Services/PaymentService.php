<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\Logger\Logger;

/**
 * Class PaymentService
 */
class PaymentService
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
	 * @param int $shopId
	 * @param int $receiptId
	 * @return array
	 */
	public function findShopPaymentByReceipt($shopId, $receiptId)
	{
		$response = $this->client->call('findShopPaymentByReceipt', [
			'shop_id'    => $shopId,
			'receipt_id' => $receiptId,
		], [], [], []);

		return $response['results'];
	}
}