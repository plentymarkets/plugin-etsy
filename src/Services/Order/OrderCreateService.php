<?php

namespace Etsy\Services\Order;

use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Order\Models\OrderItemType;
use Plenty\Plugin\Application;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;

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
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @var OrderRepositoryContract
	 */
	private $orderRepository;

	/**
	 * @var AddressRepositoryContract
	 */
	private $addressRepository;

	/**
	 * @var VariationSkuRepositoryContract
	 */
	private $variationSkuRepository;

	/**
	 * @var ContactRepositoryContract
	 */
	private $contactRepository;

	/**
	 * @var OrderHelper
	 */
	private $orderHelper;

	/**
	 * @param Application                    $app
	 * @param AddressRepositoryContract      $addressRepository
	 * @param OrderHelper                    $orderHelper
	 * @param ConfigRepository               $config
	 * @param OrderRepositoryContract        $orderRepository
	 * @param VariationSkuRepositoryContract $variationSkuRepository
	 * @param ContactRepositoryContract $contactRepository
	 */
	public function __construct(
		Application $app,
		AddressRepositoryContract $addressRepository,
		OrderHelper $orderHelper,
		ConfigRepository $config,
		OrderRepositoryContract $orderRepository,
		VariationSkuRepositoryContract $variationSkuRepository,
		ContactRepositoryContract $contactRepository
	)
	{
		$this->app                    = $app;
		$this->addressRepository      = $addressRepository;
		$this->orderHelper            = $orderHelper;
		$this->config                 = $config;
		$this->orderRepository        = $orderRepository;
		$this->variationSkuRepository = $variationSkuRepository;
		$this->contactRepository      = $contactRepository;
	}

	/**
	 * @param array $data
	 */
	public function create(array $data)
	{
		// create contact
		$contactId = $this->createContact($data);

		// create address
		$addressId = $this->createAddress($data);

		// create order
		if(!is_null($addressId))
		{
			$this->createOrder($data, $addressId);
		}
	}

	/**
	 * @param array $data
	 * @return int
	 */
	private function createContact(array $data)
	{
		return 1;
	}

	/**
	 * @param array $data
	 * @return int
	 */
	private function createAddress(array $data)
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

		$address = $this->addressRepository->createAddress($addressData);

		if($address instanceof Address)
		{
			return $address->id;
		}

		return null;
	}

	/**
	 * @param array $data
	 * @param int   $addressId
	 * @return int
	 */
	private function createOrder(array $data, $addressId)
	{
		$orderData = [
			'typeId'   => 1,
			'plentyId' => $this->app->getPlentyId(),
			'statusId' => 3.00,
			'currency' => $data['currency_code'],
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
				'value'     => (string) $data['order_id'],
			],
		];

		$orderData['addresses'] = [
			[
				'typeId'    => 1,
				'addressId' => $addressId,
			],

			[
				'typeId'    => 2,
				'addressId' => $addressId,
			],
		];

		$orderData['orderItems'] = $this->getOrderItems($data);

		$order = $this->orderRepository->createOrder($orderData);

		if($order instanceof Order)
		{
			return $order->id;
		}

		return null;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private function getOrderItems(array $data)
	{
		$orderItems = [
			[
				'typeId'  => 6,
				'itemVariationId' => 0,
				'quantity' => 1,
				'orderItemName' => 'Shipping Costs',
				'amounts' => [
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
					'referrerId'      => $this->config->get('EtsyIntegrationPlugin.referrerId'),
					'itemVariationId' => $this->matchVariationId((string) $transaction['listing_id']),
					'quantity'        => $transaction['quantity'],
					'orderItemName'   => $transaction['title'],
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
	 * @return int|null
	 */
	private function matchVariationId($sku)
	{
		$result = $this->variationSkuRepository->search([
			                                                'marketId'     => $this->config->get('EtsyIntegrationPlugin.referrerId'),
			                                                'variationSku' => $sku,
		                                                ]);

		foreach($result as $variationSku)
		{
			return $variationSku->variationId;
		}

		return 0;
	}
}
