<?php

namespace Etsy\Procedures;
use Etsy\Api\Services\ReceiptService;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Models\Legacy\Order;

/**
 * Class ShippingNotificationEventProcedure
 */
class ShippingNotificationEventProcedure
{
	/**
	 * @var ReceiptService
	 */
	private $receiptService;

	public function __construct(ReceiptService $receiptService)
	{
		$this->receiptService = $receiptService;
	}

	/**
	 * Mark an receipt as shipped on Etsy.
	 *
	 * @param EventProceduresTriggered $eventProceduresTriggered
	 */
	public function run(EventProceduresTriggered $eventProceduresTriggered)
	{
		/** @var Order $order */
		$order = $eventProceduresTriggered->getOrder();


		// TODO if order has tracking than use the submitTracking, otherwise use the updateOrder



	}
}
