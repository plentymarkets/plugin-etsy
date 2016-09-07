<?hh // strict

namespace Etsy\Services\Order;

use Etsy\Api\Client;
use Exception;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Contact\Contracts\ContactAccountRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactOptionRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Account\Contact\Models\ContactOption;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Plugin\ConfigRepository;
use Plenty\Exceptions\ValidationException;

/**
 * Class OrderImportService
 *
 * Gets the orders from Etsy and imports them into plentymarkets.
 *
 * @author plentymarkets
 *
 * @package Etsy\Service
 */
class OrderImportService
{
	/**
	 * @var Client
	 */
	private Client $client;

	/**
	 * @var ContactRepositoryContract $contactRepository
	 */
	private ContactRepositoryContract $contactRepository;

	/**
	 * @var ContactOptionRepositoryContract $contactOptionsRepository
	 */
	private ContactOptionRepositoryContract $contactOptionsRepository;

	/**
	 * @var ContactAccountRepositoryContract $accountRepository
	 */
	private ContactAccountRepositoryContract $accountRepository;

	/**
	 * @var ContactAddressRepositoryContract $addressRepository
	 */
	private ContactAddressRepositoryContract $addressRepository;

	/**
	 * @var OrderRepositoryContract $orderRepository
	 */
	private OrderRepositoryContract $orderRepository;

	/**
	 * ConfigRepository $config
	 */
	private ConfigRepository $config

	/**
	 * @param Client $client
	 * @param ContactRepositoryContract $contactRepository
	 * @param ContactOptionRepositoryContract $contactOptionsRepository
	 * @param ContactAccountRepositoryContract $accountRepository
	 * @param ContactAddressRepositoryContract $addressRepository
	 * @param OrderRepositoryContract $orderRepository
	 * @param ConfigRepository $config
	 */
	public function __construct(
		Client $client,
		ContactRepositoryContract $contactRepository,
		ContactOptionRepositoryContract $contactOptionsRepository,
		ContactAccountRepositoryContract $accountRepository,
		ContactAddressRepositoryContract $addressRepository,
		OrderRepositoryContract $orderRepository,
		ConfigRepository $config
	)
	{
		$this->client = $client;
		$this->contactRepository = $contactRepository;
		$this->contactOptionsRepository = $contactOptionsRepository;
		$this->accountRepository = $accountRepository;
		$this->addressRepository = $addressRepository;
		$this->orderRepository = $orderRepository;
		$this->config = $config;
	}

	/**
	 * Runs the order import process.
	 */
	public function run():void
	{		
		$orders = $this->getOrders();

		if(array_key_exists('results', $orders))
		{
			foreach($orders['results'] as $order)
			{					
				try
				{
					$this->validate($order);

					$this->import($order);			
				}
				catch(ValidationException $ex)
				{
					// do something here
				}				
			}			
		}	
	}

	/**
	 * Gets the orders from Etsy.
	 *
	 * @return array<string, mixed>
	 */
	public function getOrders():array<string, mixed>
	{
		return $this->client->call('findAllShopTransactions', 
			[
				'shop_id' => $this->config->get('EtsyIntegrationPlugin.shopId')
			],
			[],
			[],
			[
				'Receipt'
			]
		);
	}

	/**
	 * Checks whether order already exists to prevent duplicate imports.
	 * 
	 * @return void
	 * @throws ValidationException
	 */
	private function validate(array<string,mixed> $order):void
	{		
		$results = $this->orderRepository->searchOrders([
			'externalOrderId' => $order['transactionId']
			'referrerId' => $this->config->get('EtsyIntegrationPlugin.referrerId'),
		]);			

		if(is_array($results) && count($results))
		{
			throw new ValidationException('Order already imported');
		}

		if(!array_key_exists('receipt_id', $order))
		{
			throw new ValidationException('Receipt ID is missing');
		}

		if(!array_key_exists('results', $receipt))
		{
			throw new ValidationException('No results in order');	
		}		
	}

	/**
	 * Imports an order from Etsy to plentymarkets.
	 * @param array<string, mixed> $order
	 */
	private function import(array<string, mixed> $order):void
	{			
		$contactId = $this->createContact($order);

		$billingAddressId = $this->createAddress($this->getBillingInfo($order), $contactId);

		$shippingAddressId = $this->createAddress($this->getShippingInfo($order), $contactId);

		$orderId = $this->createOrder($order, $addressId);
	}

	/**
	 * Creates/Updates a contact with options, account and address.
	 *
	 * @param array<string, mixed> $receiptData
	 * @return int
	 */
	private function createContact(array<string, mixed> $order):int
	{
		$receipt = $this->getReceipt((int) $order['receipt_id']);		

		// TODO: if the customer exists the createContact function aborts somewhere intern		
		$contact = $this->contactRepository->createContact([
			'referrerId' => 1.00,
			'typeId' => 1, // type customer
			'firstName' => $receiptData['name'],
		]);

		// create contact options and attach to contact
		$this->contactOptionsRepository->createContactOptions([
			[
				'typeId' => 2,
				'subTypeId' => 1,
				'value' => $receiptData['buyer_email'],
				'priority' => 1
			],
		], $contact->id);

		return $contact->id;
	}

	/**
	 * Gets the receipt information of a specific transaction by receipt id.
	 *
	 * @param int $receiptId
	 * @return array<string, mixed>
	 */
	private function getReceipt(int $receiptId):array<string, mixed>
	{
		$receipt = $this->client->call('getReceipt', ['receipt_id' => $receiptId]);

		if(!array_key_exists('results', $receipt))
		{
			throw new ValidationException('Invalid receipt');
		}

		$receiptData = array_pop($receipt['results']);
	}

	/**
	 * Creates an order by given order data from Etsy.
	 *
	 * @param array<string, mixed> $order
	 * @param int $addressId
	 * @return void
	 */
	private function createOrder(array<string, mixed> $order, int $addressId):void
	{
		/*
		 * TODO: Implement.
		 * an order needs following parts:
		 * contact - contact, address
		 * items - item, quantity, price
		 * payment?
		 */
		$orderData = $this->getOrderData($order, $addressId);

		$this->orderRepository->createOrder($orderData);
	}

	/**
	 * Gets the order information from Etsy order.
	 *
	 * @param array<string, mixed> $order
	 * @param int $addressId
	 * @return array<string, mixed>
	 */
	private function getOrderData(array<string, mixed> $order, int $addressId):array<string, mixed>
	{
		$orderData =
		[
			'typeId' => 1,
			'plentyId' => 1000,	// TODO: global variable for plenty id?
			'statusId' => 3.00
		];

		// TODO:
		$orderData['orderItems'] = $this->getOrderItemsData($order);

		$orderData['addresses'] = $this->getOrderAddressData($addressId);

		return $orderData;
	}

	/**
	 * Gets the order item information from Etsy order.
	 *
	 * @param array<string, mixed> $order
	 * @return array<string, mixed>
	 */
	private function getOrderItemsData(array<string, mixed> $order):array<string, mixed>
	{
		// TODO: shipping costs?
		$orderItemsData =
		[
			// TODO:
			'typeId' => 1,
			'referrerId' => 1,
			'itemVariationId' => 1,
			'quantity' => $order['quantity'],
			'orderItemName' => $order['title'],
			'attributeValues' => 1,
			'shippingProfileId' => 1,
			'countryVatId' => 1,
			'vatField' => 1,
			//TODO: requi_redWithout: 'vatRate' => '',
		];

		$orderItemsData['amounts'] = $this->getOrderItemsAmountsData($order);

		$orderItemsData['properties'] = $this->getOrderItemsProperties($order);

		return $orderItemsData;
	}

	/**
	 * Gets the order items amounts information from Etsy order.
	 *
	 * @param array<string, mixed> $order
	 * @return array<string, mixed>
	 */
	private function getOrderItemsAmountsData(array<string, mixed> $order):array<int, mixed>
	{
		$orderItemsAmountsData =
		[
			[
				'currency' => $order['currency_code'],
				// TODO: exchangeRate - maybe need to calculate by tax/vat value
				'priceOriginalGross' => $order['total_price'], // TODO: is the total price the price without charges?
				'discount' => $order['discount_amt'],
				'isPercentage' => 0
			],
		];

		return $orderItemsAmountsData;
	}

	/**
	 * Gets the order items properties information from Etsy order.
	 *
	 * @param array<string, mixed> $order
	 * @return array<string, mixed>
	 */
	private function getOrderItemsProperties(array<string, mixed> $order):array<int, mixed>
	{
		// TODO:
		$orderItemsPropertiesData =
		[
			[
				'typeId' => 1,
				'subTypeId' => 1,
				'value' => 'value'
			],
		];

		return $orderItemsPropertiesData;
	}

	/**
	 * Gets the order address information from Etsy order.
	 *
	 * @param int $addressId
	 * @return array<string, mixed>
	 */
	private function getOrderAddressData(int $addressId):array<int, mixed>
	{
		// fill order address data
		$orderAddressesData =
		[
			[
				'typeId' => 1,
				'addressId' => $addressId
			]
		];

		return $orderAddressesData;
	}
}