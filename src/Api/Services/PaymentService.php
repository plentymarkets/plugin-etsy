<?php

namespace Etsy\Api\Services;

use Etsy\Api\Client;

/**
 * Class PaymentService
 */
class PaymentService
{
	/**
	 * @var Client
	 */
	private $client;

	public function __construct(Client $client)
	{
		$this->client = $client;
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