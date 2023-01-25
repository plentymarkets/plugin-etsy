<?php

namespace Etsy\Procedures;

use Etsy\Helper\SettingsHelper;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Models\Legacy\Order;

use Etsy\Api\Services\ReceiptService;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Plugin\ConfigRepository;

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
	 * @var ConfigRepository
	 */
	private $configRepository;

    /**
     * @param ReceiptService $receiptService
     * @param ConfigRepository $configRepository
     */
	public function __construct(ReceiptService $receiptService, ConfigRepository $configRepository)
	{
		$this->receiptService = $receiptService;
		$this->configRepository = $configRepository;
	}

	/**
	 * Mark an receipt as shipped on Etsy.
	 *
	 * @param EventProceduresTriggered $eventTriggered
	 */
	public function run(EventProceduresTriggered $eventTriggered)
	{
        if($this->checkIfCanRun() === 'true') return;

		/** @var Order $order */
		$order = $eventTriggered->getOrder();

		$this->receiptService->updateReceipt($this->getReceiptId($order), true, null);
	}

    /**
     * Return if we can run this procedure
     *
     * @return string
     */
    private function checkIfCanRun(): string
    {
        return $this->configRepository->get(SettingsHelper::PLUGIN_NAME . '.procedures', 'true');
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
