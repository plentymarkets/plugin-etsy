<?hh // strict

namespace Etsy\Service;

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

/**
 * Class OrderImportService
 *
 * Gets the orders from Etsy and imports them into plentymarkets.
 *
 * @author plentymarkets
 *
 * @package Etsy\Service
 */
class OrderImport
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
	 * OrderImportService constructor.
	 *
	 * @param Client $client
	 * @param ContactRepositoryContract $contactRepository
	 * @param ContactOptionRepositoryContract $contactOptionsRepository
	 * @param ContactAccountRepositoryContract $accountRepository
	 * @param ContactAddressRepositoryContract $addressRepository
	 */
	public function __construct(Client $client,
								ContactRepositoryContract $contactRepository,
								ContactOptionRepositoryContract $contactOptionsRepository,
								ContactAccountRepositoryContract $accountRepository,
								ContactAddressRepositoryContract $addressRepository,
								OrderRepositoryContract $orderRepositoryContract)
	{
		$this->client = $client;
		$this->contactRepository = $contactRepository;
		$this->contactOptionsRepository = $contactOptionsRepository;
		$this->accountRepository = $accountRepository;
		$this->addressRepository = $addressRepository;
		$this->orderRepository = $orderRepositoryContract;
	}

	/**
	 * Runs the order import process.
	 */
	public function run():void
	{

		// 1. Get orders from Etsy
		$orders = $this->getOrders();

		if(!is_null($orders) && is_array($orders))
		{
			$importedOrders = 0;

			foreach($orders['results'] as $order)
			{
				try
				{
					// TODO: 2. Implement method to check/validate order for import
					if(!$this->isValid())
					{
//						// TODO: Excetpion - invalid order/order exists to prevent duplicates
					}

					// 3. Import orders
					$this->import($order);

					$importedOrders++;
				}
				catch(Exception $e)
				{
					// TODO: Log
				}
			}

			// TODO: Log, order import result, like 'x new orders imported' or '2/4 imported'.
		}
		else
		{
			// TODO: Log, no response/no orders
		}
	}

	/**
	 * Gets the orders from Etsy.
	 *
	 * @return ?array<string, mixed>
	 */
	public function getOrders():?array<string, mixed>
	{
		return $this->client->call('findAllShopTransactions', ['shop_id' => 13651803]);
	}

	/**
	 * Gets the receipt information of a specific transaction by receipt id.
	 *
	 * @param int $receiptId
	 * @return ?array<string, mixed>
	 */
	private function getReceiptData(int $receiptId):?array<string, mixed>
	{
		return $this->client->call('getReceipt', ['receipt_id' => $receiptId]);
	}



	/**
	 * Checks whether order already exists to prevent duplicate imports.
	 */
	private function isValid():bool
	{
		// TODO: Implement. There is no function findByExternal order id. Waiting for team order..
		return true;
	}

	/**
	 * Imports an order from Etsy to plentymarkets.
	 * @param array<string, mixed> $order
	 */
	private function import(array<string, mixed> $order):void
	{
		$receiptData = $this->getReceiptData((int) $order['receipt_id']);

		if(!is_null($receiptData) && is_array($receiptData))
		{
			$addressId = $this->createContact(array_pop($receiptData['results']));

			$this->createOrder($order, $addressId);
		}
	}

	/**
	 * Creates/Updates a contact with options, account and address.
	 *
	 * @param array<string, mixed> $receiptData
	 * @return int
	 */
	private function createContact(array<string, mixed> $receiptData):int
	{
		// TODO: if the customer exists the createContact function aborts somewhere intern
		// create contact
		$contact = $this->contactRepository->createContact($this->getContactData($receiptData));

		// create contact options and attach to contact
		$this->contactOptionsRepository->createContactOptions($this->getContactOptionsData($receiptData), $contact->id);

		// create address and attack to contact
		$address = $this->addressRepository->createAddress($this->getAddressData($receiptData), $contact->id, 1);	// TODO: wtf is the type id
		$contact->addresses->add($address);

		return $address->id;
	}

	/**
	 * Gets the contact information from Etsy order.
	 *
	 * @param array<string, mixed> $receiptData
	 * @return array<string, mixed>
	 */
	private function getContactData(array<string, mixed> $receiptData):array<string, mixed>
	{
		// fillable fields
		$contactData =
		[
			'referrerId' => 1.00,
			'typeId' => 1, // type customer
			'firstName' => $receiptData['name'],
		];

		return $contactData;
	}

	/**
	 * Gets the contact options, like email, from Etsy order.
	 *
	 * @param array<string, mixed> $receiptData
	 * @return array<mixed>
	 */
	private function getContactOptionsData(array<string, mixed> $receiptData):array<mixed>
	{
		$contactOptionsData =
		[
			[
				'typeId' => 2,
				'subTypeId' => 1,
				'value' => $receiptData['buyer_email'],
				'priority' => 1
			],
		];

		return $contactOptionsData;
	}

	/**
	 * Gets the address information from Etsy order.
	 *
	 * @param array<string, mixed> $receiptData
	 * @return array<string, mixed>
	 */
	private function getAddressData(array<string, mixed> $receiptData):array<string, mixed>
	{
		$addressData =
		[
			'name2' => $receiptData['name'],
			'name3' => '',
			'address1' => $receiptData['first_line'],
			// TODO: address2 - house no.
			'postalCode' => $receiptData['zip'],
			'town' => $receiptData['city'],
			'stateId' => 1, // TODO: receiptData 'state'
			'countryId' => 49 // TODO: countryId - make call, map by iso code and get id.
		];

		return $addressData;
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