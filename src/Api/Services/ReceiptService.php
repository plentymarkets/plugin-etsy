<?php

namespace Etsy\Api\Services;

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

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Retrieves a set of Receipt objects associated to a Shop.
	 *
	 * @param int    $shopId
	 * @param string $from
	 * @param string $to
	 *
	 * @return array
	 */
	public function findAllShopReceipts($shopId, $from, $to)
	{
		return $this->client->call('findAllShopReceipts', [
			'shop_id' => $shopId,
		], [
			                                'min_created' => strtotime($from),
			                                'max_created' => strtotime($to),
		                                ], [], [
			                                'Transactions' => 'Transactions',
			                                'Buyer'        => 'Buyer',
		                                ]);
	}

	/**
	 * Updates a Shop_Receipt2
	 *
	 * @param int       $receiptId
	 * @param bool|null $wasPaid
	 * @param bool|null $wasShipped
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function updateReceipt($receiptId, $wasPaid = null, $wasShipped = null)
	{
		$data = [];

		if($wasPaid)
		{
			$data['was_paid'] = $wasPaid;
		}

		if($wasShipped)
		{
			$data['was_shipped'] = $wasShipped;
		}

		$response = $this->client->call('updateReceipt', [
			'receipt_id' => $receiptId,
		], $data);

		return $response;
	}

	/**
	 * Submits tracking information and sends a shipping notification email to the buyer. If send_bcc is true, the
	 * shipping notification will be sent to the seller as well.
	 *
	 * @param int    $shopId
	 * @param int    $receiptId
	 * @param string $trackingCode
	 * @param string $carrierName
	 * @param bool   $sendBcc
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function submitTracking($shopId, $receiptId, $trackingCode, $carrierName, $sendBcc = false)
	{
		$response = $this->client->call('submitTracking', [
			'shop_id'    => $shopId,
			'receipt_id' => $receiptId,
		], [
			                                'tracking_code' => $trackingCode,
			                                'carrier_name'  => $carrierName,
			                                'send_bcc'      => $sendBcc
		                                ]);

		return $response;
	}
}
