<?php

namespace Etsy\Procedures;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Models\Legacy\Order;

/**
 * Class ShippingNotificationEventProcedure
 */
class ShippingNotificationEventProcedure
{
	/**
	 * Mark an receipt as shipped on Etsy.
	 *
	 * @param EventProceduresTriggered $eventProceduresTriggered
	 */
	public function run(EventProceduresTriggered $eventProceduresTriggered)
	{
		/** @var Order $order */
		$order = $eventProceduresTriggered->getOrder();


		// TODO stuff

	}
}
