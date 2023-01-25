<?php

namespace Etsy\Procedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Legacy\Order;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;

use Etsy\Api\Services\ReceiptService;
use Etsy\Helper\SettingsHelper;
use Etsy\Helper\ShippingHelper;
use Plenty\Modules\Order\Shipping\ParcelService\Models\ParcelServicePreset;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ShippingNotificationEventProcedure
 */
class ShippingNotificationEventProcedure
{
	use Loggable;

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
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @param ReceiptService $receiptService
     * @param ShippingHelper $shippingHelper
     * @param SettingsHelper $settingsHelper
     * @param ConfigRepository $configRepository
     */
	public function __construct(ReceiptService $receiptService, ShippingHelper $shippingHelper, SettingsHelper $settingsHelper, ConfigRepository $configRepository)
	{
		$this->receiptService = $receiptService;
		$this->shippingHelper = $shippingHelper;
		$this->settingsHelper = $settingsHelper;
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

		$trackingCode = $this->getTrackingCode($order);
        $this->getLogger(__METHOD__)
            ->addReference("orderId", $order->id)
            ->debug("log/markets/global.debugInformation", [
                "shippingProfileId" => $order->shippingProfileId
            ]);
		$carrierName  = $this->getCarrierName($order);

		if(strlen($carrierName) && strlen($trackingCode))
		{
			$this->receiptService->submitTracking($this->settingsHelper->getShopSettings('shopId'), $this->getReceiptId($order), $trackingCode, $carrierName, $order->id, true);
		}
		else
		{
			$this->receiptService->updateReceipt($this->getReceiptId($order), null, true);
		}
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
			$this->getLogger(__FUNCTION__)
                ->addReference('orderId', $order->id)
				->error('Etsy::order.trackingCodeError', $ex->getMessage());
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
            $parcelServiceTypeId = $parcelServicePreset->parcelService->parcel_service_type;
            $parcelServiceNameItem = $parcelServicePreset->parcelServiceNames->first();
            $parcelServiceName = $parcelServiceNameItem->name;

            $this->getLogger(__METHOD__)
                ->addReference('orderId', $order->id)
                ->debug('log/markets/global.debugInformation', [
                    'parcelService'             => $parcelServicePreset->parcelService,
                    'parcelServiceType'         => $parcelServiceTypeId,
                    'parcelServiceName'         => $parcelServiceName,
                    'shippingServiceProviderId' => $parcelServicePreset->parcelService->shippingServiceProviderId
                ]);

			if($parcelServicePreset instanceof ParcelServicePreset)
			{
				return $this->shippingHelper->getCarrierCode($parcelServiceTypeId, $parcelServiceName);
			}
		}
		catch(\Exception $ex)
		{
			$this->getLogger(__FUNCTION__)
				->setReferenceType('orderId')
				->setReferenceValue($order->id)
				->error('Etsy::order.carrierNameError', $ex->getMessage());
		}

		return null;
	}
}
