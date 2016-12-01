<?php

namespace Etsy\Procedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Models\Legacy\Order;

use Etsy\Api\Services\ReceiptService;
use Etsy\Logger\Logger;

/**
 * Class PaymentNotificationEventProcedure
 */
class PaymentNotificationEventProcedure
{
	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var ReceiptService
	 */
	private $receiptService;

	/**
	 * @param Logger         $logger
	 * @param ReceiptService $receiptService
	 */
	public function __construct(Logger $logger, ReceiptService $receiptService)
	{
		$this->logger         = $logger;
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
		if($order->properties->where('typeId', 14)->first())
		{
			return $order->properties->where('typeId', 14)->first()->value;
		}

		return null;
	}
}
