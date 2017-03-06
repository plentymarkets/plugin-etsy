<?php

namespace Etsy\Procedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Models\Legacy\Order;

use Etsy\Api\Services\ReceiptService;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;

/**
 * Class PaymentNotificationEventProcedure
 */
class PaymentNotificationEventProcedure
{
	/**
	 * @var ReceiptService
	 */
	private $receiptService;

	/**
	 * @param ReceiptService $receiptService
	 */
	public function __construct(ReceiptService $receiptService)
	{
		$this->receiptService = $receiptService;
	}

	/**
	 * Mark an receipt as shipped on Etsy.
	 *
	 * @param EventProceduresTriggered $eventTriggered
	 */
	public function run(EventProceduresTriggered $eventTriggered)
	{
		/** @var Order $order */
		$order = $eventTriggered->getOrder();

		$this->receiptService->updateReceipt($this->getReceiptId($order), true, null);
	}

	/**
	 * Get the receipt ID from the order properties.
	 *
	 * @param Order $order
	 *
	 * @return int|null
	 */
	private function getReceiptId($order)
	{
		if($order->properties->where('typeId', OrderPropertyType::EXTERNAL_ORDER_ID)->first())
		{
			return $order->properties->where('typeId', OrderPropertyType::EXTERNAL_ORDER_ID)->first()->value;
		}

		return null;
	}
}
