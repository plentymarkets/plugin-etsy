<?php

namespace Etsy\Services\Order;

use Etsy\Api\Services\PaymentService;
use Etsy\Helper\OrderHelper;
use Etsy\Helper\PaymentHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Accounting\Contracts\AccountingServiceContract;
use Plenty\Modules\Accounting\Vat\Contracts\VatInitContract;
use Plenty\Modules\Accounting\Vat\Models\Vat;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Models\OrderItemType;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Plugin\Application;
use Plenty\Plugin\Log\Loggable;

/**
 * Class OrderCreateService
 */
class OrderCreateService
{
	use Loggable;

	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @var OrderHelper
	 */
	private $orderHelper;

	/**
	 * @var SettingsHelper
	 */
	private $settingsHelper;

	/**
	 * @param Application    $app
	 * @param OrderHelper    $orderHelper
	 * @param SettingsHelper $settingsHelper
	 */
	public function __construct(Application $app, OrderHelper $orderHelper, SettingsHelper $settingsHelper)
	{
		$this->app            = $app;
		$this->orderHelper    = $orderHelper;
		$this->settingsHelper = $settingsHelper;
	}

	/**
	 * @param array $data
	 */
	public function create(array $data)
	{
		// create contact
		$contactId = $this->createContact($data);

		// create address
		$addressId = $this->createAddress($contactId, $data);

		// create order
		if(!is_null($addressId))
		{
			$order = $this->createOrder($data, $addressId, $contactId);

			if($this->orderHelper->isDirectCheckout((string) $data['payment_method']))
			{
				$this->createPayment($data, $order);
			}
			else
			{
				$this
					->getLogger(__FUNCTION__)
					->setReferenceType('orderId')
					->setReferenceValue($order->id)
					->info('Etsy::order.paymentNotCreated');
			}
		}
	}

	/**
	 * Create contact for current customer.
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	private function createContact(array $data): int
	{
		$name = $this->orderHelper->extractName($data['name']);

		$contactData = [
			'typeId'     => 1,
			'referrerId' => $this->orderHelper->getReferrerId(),
			'externalId' => $data['buyer_user_id'],
			'firstName'  => $name['firstName'],
			'lastName'   => $name['lastName'],
			'options'    => [
				[
					'typeId'    => 10,
					'subTypeId' => 11,
					'priority'  => 0,
					'value'     => '1',
				]
			],
		];

		if(isset($data['buyer_email']) && strlen($data['buyer_email']))
		{
			$contactData['options'][] = [
				'typeId'    => 2,
				'subTypeId' => 4,
				'priority'  => 0,
				'value'     => $data['buyer_email'],
			];
		}

		/** @var ContactRepositoryContract $contactRepo */
		$contactRepo = pluginApp(ContactRepositoryContract::class);

		$contact = $contactRepo->createContact($contactData);

		$this
			->getLogger(__FUNCTION__)
			->addReference('etsyReceiptId', $data['receipt_id'])
			->addReference('contactId', $contact->id)
			->info('Etsy::order.contactCreated');

		return $contact->id;
	}

	/**
	 * @param int   $contactId
	 * @param array $data
	 *
	 * @return int
	 */
	private function createAddress(int $contactId, array $data): int
	{
		$addressData = [
			'name2'                    => $data['name'],
			'address1'                 => $this->orderHelper->getStreetName((string) $data['first_line']),
			'address2'                 => $this->orderHelper->getHouseNumber((string) $data['first_line']),
			'postalCode'               => $data['zip'],
			'town'                     => $data['city'],
			'countryId'                => $this->orderHelper->getCountryIdByEtsyCountryId((int) $data['country_id']),
			'useAddressLightValidator' => true,
		];

		if(isset($addressData['countryId']) && $addressData['countryId'] > 0 && isset($data['state']) && strlen($data['state']))
		{
			$addressData['stateId'] = $this->orderHelper->getStateIdByCountryIdAndIsoCode($addressData['countryId'], $data['state']);
		}

		if(isset($data['second_line']) && strlen($data['second_line']))
		{
			$addressData['address3'] = $data['second_line'];
		}

		$addressData['options'] = [
			[
				'typeId' => 5,
				'value'  => $data['buyer_email'],
			],
		];

		/** @var ContactAddressRepositoryContract $contactAddressRepo */
		$contactAddressRepo = pluginApp(ContactAddressRepositoryContract::class);

		$address = $contactAddressRepo->createAddress($addressData, $contactId, 2);

		$this
			->getLogger(__FUNCTION__)
			->addReference('etsyReceiptId', $data['receipt_id'])
			->addReference('contactId', $contactId)
			->addReference('addressId', $address->id)
			->info('Etsy::order.addressCreated');

		return $address->id;
	}

	/**
	 * @param array $data
	 * @param int   $addressId
	 * @param int   $contactId
	 *
	 * @return Order
	 */
	private function createOrder(array $data, $addressId, $contactId): Order
	{
		// TODO add also the message_from_buyer(string) to the order

		$orderData = [
			'typeId'     => 1,
			'referrerId' => $this->orderHelper->getReferrerId(),
			'plentyId'   => $this->app->getPlentyId(),
			'statusId'   => 3.00,
			'currency'   => $data['currency_code'],
		];

		$orderData['properties'] = [
			[
				'typeId' => OrderPropertyType::PAYMENT_METHOD,
				'value'  => (string) $this->orderHelper->getPaymentMethodId((string) $data['payment_method']),
			],

			[
				'typeId' => OrderPropertyType::EXTERNAL_ORDER_ID,
				'value'  => (string) $data['receipt_id'],
			],
		];

		$orderData['addressRelations'] = [
			[
				'typeId'    => 1,
				'addressId' => $addressId,
			],

			[
				'typeId'    => 2,
				'addressId' => $addressId,
			],
		];

		$orderData['relations'] = [
			[
				'referenceType' => 'contact',
				'referenceId'   => $contactId,
				'relation'      => 'receiver',
			]
		];

		$orderData['orderItems'] = $this->getOrderItems($data);

		/** @var OrderRepositoryContract $orderRepo */
		$orderRepo = pluginApp(OrderRepositoryContract::class);

		$order = $orderRepo->createOrder($orderData);

		$this
			->getLogger(__FUNCTION__)
			->addReference('etsyReceiptId', $data['receipt_id'])
			->addReference('contactId', $contactId)
			->addReference('addressId', $addressId)
			->addReference('orderId', $order->id)
			->info('Etsy::order.orderCreated');

		return $order;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	private function getOrderItems(array $data)
	{
		$orderItems = [
			[
				'typeId'          => 6,
				'itemVariationId' => 0,
				'quantity'        => 1,
				'orderItemName'   => 'Shipping Costs',
				'countryVatId'    => $this->getVatId($data),
				'amounts'         => [
					[
						'priceOriginalGross' => $data['total_shipping_cost'],
						'currency'           => $data['currency_code'],
					],
				],
			],
		];

		$transactions = $data['Transactions'];

		if(is_array($transactions))
		{
			foreach($transactions as $transaction)
			{
				$itemVariationId = $this->matchVariationId((string) $transaction['listing_id']);

				$orderItems[] = [
					'typeId'          => $itemVariationId > 0 ? 1 : 9,
					'referrerId'      => $this->orderHelper->getReferrerId(),
					'itemVariationId' => $this->matchVariationId((string) $transaction['listing_id']),
					'quantity'        => $transaction['quantity'],
					'orderItemName'   => $transaction['title'],
					'countryVatId'    => $this->getVatId($data),
					'amounts'         => [
						[
							'priceOriginalGross' => $transaction['price'],
							'currency'           => $transaction['currency_code'],
						],
					],
					'properties'      => [
						[
							'typeId'    => 10,
							'subTypeId' => 6,
							'value'     => (string) $transaction['listing_id'],
						],
						[
							'typeId'    => 12,
							'subTypeId' => 6,
							'value'     => (string) $transaction['transaction_id'],
						],
					],
				];
			}

			// add coupon item position
			if(isset($data['discount_amt']) && $data['discount_amt'] > 0)
			{
				$orderItems[] = [
					'typeId'          => OrderItemType::TYPE_UNASSIGEND_VARIATION,
					'referrerId'      => $this->orderHelper->getReferrerId(),
					'quantity'        => 1,
					'orderItemName'   => 'Coupon',
					'countryVatId'    => $this->getVatId($data),
					'amounts'         => [
						[
							'priceOriginalGross' => -$data['discount_amt'],
							'currency'           => $data['currency_code'],
						],
					],
				];
			}
		}

		return $orderItems;
	}

	/**
	 * @param string $sku
	 *
	 * @return int|null
	 */
	private function matchVariationId($sku)
	{
		/** @var VariationSkuRepositoryContract $variationSkuRepo */
		$variationSkuRepo = pluginApp(VariationSkuRepositoryContract::class);

		$result = $variationSkuRepo->search([
			                                    'marketId' => $this->orderHelper->getReferrerId(),
			                                    'sku'      => $sku,
		                                    ]);

		foreach($result as $variationSku)
		{
			return $variationSku->variationId;
		}

		return 0;
	}

	/**
	 * Create payment.
	 *
	 * @param array $data
	 * @param Order $order
	 */
	private function createPayment(array $data, Order $order)
	{
		try
		{
			/** @var PaymentService $paymentService */
			$paymentService = pluginApp(PaymentService::class);

			/** @var PaymentRepositoryContract $paymentRepo */
			$paymentRepo = pluginApp(PaymentRepositoryContract::class);

			$payments = $paymentService->findShopPaymentByReceipt($this->settingsHelper->getShopSettings('shopId'), $data['receipt_id']);

			if(is_array($payments) && count($payments))
			{
				/** @var PaymentHelper $paymentHelper */
				$paymentHelper = $this->app->make(PaymentHelper::class);

				foreach($payments as $paymentData)
				{
					/** @var Payment $payment */
					$payment                  = $this->app->make(Payment::class);
					$payment->amount          = $paymentData['amount_gross'] / 100;
					$payment->mopId           = $paymentHelper->getPaymentMethodId();
					$payment->currency        = $paymentData['currency'];
					$payment->status          = 2;
					$payment->transactionType = 2;

					$paymentProperties = [];

					$paymentProperties[] = $this->createPaymentProperty(PaymentProperty::TYPE_TRANSACTION_ID, $paymentData['payment_id']);

					$paymentProperties[] = $this->createPaymentProperty(PaymentProperty::TYPE_ORIGIN, Payment::ORIGIN_PLUGIN);

					$payment->properties = $paymentProperties;

					$payment = $paymentRepo->createPayment($payment);

					/** @var PaymentOrderRelationRepositoryContract $paymentOrderRelation */
					$paymentOrderRelation = $this->app->make(PaymentOrderRelationRepositoryContract::class);

					$paymentOrderRelation->createOrderRelation($payment, $order);

					$this
						->getLogger(__FUNCTION__)
						->addReference('orderId', $order->id)
						->addReference('paymentId', $payment->id)
						->info('Etsy::order.paymentAssigned', [
							'amount'            => $payment->amount,
							'methodOfPaymentId' => $payment->mopId,
						]);
				}
			}
			else
			{
				$this
					->getLogger(__FUNCTION__)
					->setReferenceType('orderId')
					->setReferenceValue($order->id)
					->info('Etsy::order.paymentNotFound', [
						'receiptId' => $data['receipt_id'],
					]);
			}
		}
		catch(\Exception $ex)
		{
			$this->getLogger(__FUNCTION__)
				 ->addReference('orderId', $order->id)
			     ->error('Etsy::order.paymentError', $ex->getMessage());
		}
	}

	/**
	 * Get the VAT ID.
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	private function getVatId(array $data): int
	{
		/** @var VatInitContract $vatInit */
		$vatInit = pluginApp(VatInitContract::class);

		/** @var AccountingServiceContract $accountingService */
		$accountingService = pluginApp(AccountingServiceContract::class);

		$vatInit->init($this->orderHelper->getCountryIdByEtsyCountryId((int) $data['country_id']), '', $accountingService->detectLocationId($this->app->getPlentyId()), $this->orderHelper->getCountryIdByEtsyCountryId((int) $data['country_id']));

		$vat = $vatInit->getStandardVatByLocationId($accountingService->detectLocationId($this->app->getPlentyId()));

		if($vat instanceof Vat)
		{
			return $vat->id;
		}

		return 0;
	}

	/**
	 * Create a payment property based on a given type ID and value.
	 *
	 * @param int   $typeId
	 * @param mixed $value
	 *
	 * @return PaymentProperty
	 */
	private function createPaymentProperty(int $typeId, $value): PaymentProperty
	{
		/** @var PaymentProperty $paymentProperty */
		$paymentProperty         = pluginApp(PaymentProperty::class);
		$paymentProperty->typeId = $typeId;
		$paymentProperty->value  = $value;

		return $paymentProperty;
	}
}
