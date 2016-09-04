<?hh //strict

namespace Etsy\Service;

use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Contact\Contracts\ContactOptionRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Account\Contracts\AccountRepositoryContract;
use Plenty\Modules\Account\Models\Account;
use Plenty\Modules\Order\Models\Order;

/**
 * Class OrderImportService
 *
 * Gets the orders from Etsy and imports them into plentymarkets.
 *
 * @author ngrau
 *
 * @package Etsy\Service
 */
class OrderImportService
{
	private $config;


	public function run()
	{
		// TODO: Implement. Need plugin config with basic settings.
		$this->config = $this->getConfig();

		// TODO: Implement. Do rest call here to get transactions.
		$orders = $this->getOrders();

		// TODO: Implement. Got response? Import each order.
		if(isset($orders))
		{
			foreach($orders as $order)
			{
				$this->import($order);
			}
		}
	}

	public function getConfig()
	{
		// TODO: Maybe implement in __construct() instead here. Requires plugin config with basic settings.
	}

	public function getOrders()
	{
		// TODO: Do rest call here to get orders from etsy (with OAuth).
	}

	/**
	 * Imports an order from Etsy to plentymarkets.
	 *
	 * @param $order
	 */
	public function import($order)
	{
		// TODO: Check the Etsy order response structure and give correct params to the functions.
		$contact = $this->createContact($order->customerData);
		$this->createOrder($order->orderData);
	}

	/**
	 * Creates a contact with options (like email or phone), account and address.
	 *
	 * @param array $customerData
	 * @return Contact
	 */
	public function createContact($customerData):Contact
	{
		/** @var ContactRepositoryContract $contactRepository */
		$contactRepository = App::make(ContactRepositoryContract::class);

		// fill contact data
		// TODO: Requires an etsy order too see response structure and fill values.
		$contactData = array
		(
			// fillable fields
			'referrerId' => 1.00, 		// TODO whats referrerId? Note: All orders in local db got 1.00
			'typeId' => 1,				// type customer
            'externalId' => ,
            'number' => ,
            'firstName' => ,
            'lastName' => ,
            'gender' => ,
            'formOfAddress' => ,
            'newsletterAllowanceAt' => ,
            'classId' => ,
            'password' => ,
            'blocked' => ,
            'rating' => ,
            'bookAccount' => ,
            'lang' => ,
            'webstoreId' => ,
            'userId' => ,
            'birthdayAt' => ,
            'lastLoginAt' => ,
            'lastOrderAt' =>
		);

		$contact = $contactRepository->createContact($contactData);

		// create contact options and attach to contact
		$this->createContactOptions($contact->id, $customerData);

		// create account and attach to contact
		$this->createAccount($contact, $customerData);

		// create address and attack to contact
		$this->createAddress($contact, $customerData);

		return $contact;
	}

	/**
	 * Creates the contact options, like email or phone, and attach it to the given contact.
	 *
	 * @param int $contactId
	 * @param array $customerData
	 */
	public function createContactOptions($contactId, $customerData)
	{
		/** @var ContactOptionRepositoryContract $contactOptionsRepository */
		$contactOptionsRepository = App::make(ContactOptionRepositoryContract::class);

		// fill contact options data
		// TODO: Requires an etsy order too see response structure and fill values.
		$contactOptionsData = array
		(
			// fillable fields
			[
				'typeId' => ,
				'subTypeId' => ,
				'value' => ,
				'priority' =>
			],
			[
				'typeId' => ,
				'subTypeId' => ,
				'value' => ,
				'priority' =>
			],
		);

		// create and attach contact options to contact
		$contactOptionsRepository->createContactOptions($contactOptionsData, $contactId);
	}

	/**
	 * Creates an account and attach it to the given contact.
	 *
	 * @param Contact $contact
	 * @param array $customerData
	 * @return Account
	 */
	public function createAccount(Contact $contact, $customerData):Account
	{
		/** @var AccountRepositoryContract $accountRepository */
		$accountRepository = App::make(AccountRepositoryContract::class);

		// fill account data
		// TODO: Requires an etsy order too see response structure and fill values.
		$accountData = array
		(
			// fillable fields
			'number' => ,
			'companyName' => ,
			'taxIdNumber' => ,
			'valuta' => ,
			'discountDays' => ,
			'discountPercent' => ,
			'timeForPaymentAllowedDays' => ,
			'salesRepresentativeContactId' => ,
			'userId' =>
		);

		// create and attach account data to contact
		$account = $accountRepository->createAccount($accountData);
		$contact->accounts->add($account);
	}

	/**
	 * Creates an address and attach it to the given contact.
	 *
	 * @param Contact $contact
	 * @param array $customerData
	 * @return Address
	 */
	public function createAddress(Contact $contact, $customerData):Address
	{
		/** @var AddressRepositoryContract $addressRepository */
		$addressRepository = App::make(AddressRepositoryContract::class);

		// fill address data
		// TODO: Requires an etsy order too see response structure and fill values.
		$addressData = array
		(
			// TODO: name1 is just an example, depends on etsy order response to fill values
			// fillable fields
			'name1' => ,
			'name2' => ,
			'name3' => ,
			'name4' => ,
			'address1' => ,
			'address2' => ,
			'address3' => ,
			'address4' => ,
			'postalCode' => ,
			'town' => ,
			'countryId' => ,
			'stateId' => ,
			'readOnly' => ,
			'checkedAt' =>
		);

		// create and attach address to contact
		$address = $addressRepository->createAddress($addressData);
		$contact->addresses->add($address);
	}


	/**
	 * Creates an order by given order data from Etsy.
	 *
	 * @param $orderData
	 * @return Order
	 */
	public function createOrder($orderData):Order
	{
		// TODO: create order.
		/*
		 * An order requires following parts:
		 * Contact (Contact, Address)
		 * Items (Item, Quantity, Price)
		 * Payment?
		 */
	}
}