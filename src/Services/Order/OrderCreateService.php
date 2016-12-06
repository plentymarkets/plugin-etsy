<?php

namespace Etsy\Services\Order;

use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Accounting\Contracts\AccountingServiceContract;
use Plenty\Modules\Accounting\Vat\Contracts\VatInitContract;
use Plenty\Modules\Accounting\Vat\Models\Vat;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Plugin\Application;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;

use Etsy\Logger\Logger;
use Etsy\Api\Services\PaymentService;
use Etsy\Helper\PaymentHelper;
use Etsy\Helper\SettingsHelper;
use Etsy\Helper\OrderHelper;

/**
 * Class OrderCreateService
 */
class OrderCreateService
{
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
	 * @var Logger
	 */
	private $logger;

	/**
	 * @param Application    $app
	 * @param OrderHelper    $orderHelper
	 * @param SettingsHelper $settingsHelper
	 * @param Logger         $logger
	 */
	public function __construct(Application $app, OrderHelper $orderHelper, SettingsHelper $settingsHelper, Logger $logger)
	{
		$this->app            = $app;
		$this->orderHelper    = $orderHelper;
		$this->settingsHelper = $settingsHelper;
		$this->logger         = $logger;
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
		}
	}

	/**
	 * Create contact for current customer.
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	private function createContact(array $data):int
	{
		$name = $this->orderHelper->extractName($data['name']);

		$contactData = [
			'typeId'     => 1,
			'referrerId' => $this->orderHelper->getReferrerId(),
			'externalId' => $data['buyer_user_id'],
			'firstName'  => $name['firstName'],
			'lastName'   => $name['lastName'],
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

		return $contact->id;
	}

	/**
	 * @param int   $contactId
	 * @param array $data
	 *
	 * @return int
	 */
	private function createAddress(int $contactId, array $data):int
	{
		$addressData = [
			'name2'      => $data['name'],
			'address1'   => $this->orderHelper->getStreetName((string) $data['first_line']),
			'address2'   => $this->orderHelper->getHouseNumber((string) $data['first_line']),
			'postalCode' => $data['zip'],
			'town'       => $data['city'],
			'countryId'  => $this->orderHelper->getCountryIdByEtsyCountryId((int) $data['country_id']),
		];

		$addressData['options'] = [
			[
				'typeId' => 5,
				'value'  => $data['buyer_email'],
			],
		];

		/** @var ContactAddressRepositoryContract $contactAddressRepo */
		$contactAddressRepo = pluginApp(ContactAddressRepositoryContract::class);

		$address = $contactAddressRepo->createAddress($addressData, $contactId, 2);

		return $address->id;
	}

	/**
	 * @param array $data
	 * @param int   $addressId
	 * @param int   $contactId
	 *
	 * @return Order
	 */
	private function createOrder(array $data, $addressId, $contactId):Order
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

			// method of payment
			[
				'typeId'    => 13,
				'subTypeId' => 1,
				'value'     => (string) $this->orderHelper->getPaymentMethodId((string) $data['payment_method']),
			],

			// external order id
			[
				'typeId'    => 14,
				'subTypeId' => 6,
				'value'     => (string) $data['receipt_id'],
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
			                                    'marketId'     => $this->orderHelper->getReferrerId(),
			                                    'variationSku' => $sku,
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

					$payment = $paymentRepo->createPayment($payment);

					/** @var PaymentOrderRelationRepositoryContract $paymentOrderRelation */
					$paymentOrderRelation = $this->app->make(PaymentOrderRelationRepositoryContract::class);

					$paymentOrderRelation->createOrderRelation($payment, $order);
				}
			}
		}
		catch(\Exception $ex)
		{
			$this->logger->log('Can not add payment: ' . $ex->getMessage());
		}
	}

	/**
	 * Get the VAT ID.
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	private function getVatId(array $data):int
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
}
