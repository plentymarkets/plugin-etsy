<?php

namespace Etsy\Procedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Legacy\Order;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;

use Etsy\Api\Services\ReceiptService;
use Etsy\Helper\SettingsHelper;
use Etsy\Helper\ShippingHelper;

/**
 * Class ShippingNotificationEventProcedure
 */
class ShippingNotificationEventProcedure
{
	/**
	 * @var ReceiptService
	 */
	private $receiptService;

	/**
	 * @var ShippingHelper
	 */
	private $shippingHelper;

	/**
	 * @var SettingsHelper
	 */
	private $settingsHelper;

	/**
	 * @param ReceiptService $receiptService
	 * @param ShippingHelper $shippingHelper
	 * @param SettingsHelper $settingsHelper
	 */
	public function __construct(ReceiptService $receiptService, ShippingHelper $shippingHelper, SettingsHelper $settingsHelper)
	{
		$this->receiptService = $receiptService;
		$this->shippingHelper = $shippingHelper;
		$this->settingsHelper = $settingsHelper;
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

		$trackingCode = $this->getTrackingCode($order);
		$carrierName  = $this->getCarrierName($order);

		if(strlen($carrierName) && strlen($trackingCode))
		{
			$this->receiptService->submitTracking($this->settingsHelper->getShopSettings('shopId'), $this->getReceiptId($order), $trackingCode, $carrierName, true);
		}
		else
		{
			$this->receiptService->updateReceipt($this->getReceiptId($order), null, true);
		}
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

	/**
	 * Get tracking code.
	 *
	 * @param Order $order
	 *
	 * @return mixed|null
	 */
	private function getTrackingCode($order)
	{
		try
		{
			/** @var OrderRepositoryContract $orderRepo */
			$orderRepo = pluginApp(OrderRepositoryContract::class);

			$packageNumbers = $orderRepo->getPackageNumbers($order->id);

			if(is_array($packageNumbers) && count($packageNumbers))
			{
				return $packageNumbers[0];
			}
		}
		catch(\Exception $ex)
		{
			// $this->logger->log('Can not get tracking code for order id ' . $order->id . ': ' . $ex->getMessage());
		}

		return null;
	}

	/**
	 * Get the carrier name base on the order shipping profile.
	 *
	 * @param Order $order
	 *
	 * @return mixed|null
	 */
	private function getCarrierName($order)
	{
		try
		{
			/** @var ParcelServicePresetRepositoryContract $parcelServicePresetRepo */
			$parcelServicePresetRepo = pluginApp(ParcelServicePresetRepositoryContract::class);

			$parcelServicePreset = $parcelServicePresetRepo->getPresetById($order->shippingProfileId);

			return $this->shippingHelper->getCarrierCode($parcelServicePreset->parcelService->parcel_service_type);
		}
		catch(\Exception $ex)
		{
			// $this->logger->log('Can not get carrier name for order id ' . $order->id . ': ' . $ex->getMessage());
		}

		return null;
	}
}
