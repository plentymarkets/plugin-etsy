<?hh //strict

namespace Etsy\Service;

use Etsy\Api\Client;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Contact\Contracts\ContactOptionRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Account\Contracts\AccountRepositoryContract;
use Plenty\Modules\Account\Models\Account;
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
	 * @var AccountRepositoryContract $accountRepository
	 */
	private AccountRepositoryContract $accountRepository;

	/**
	 * @var AddressRepositoryContract $addressRepository
	 */
	private AddressRepositoryContract $addressRepository;




	public function __construct(Client $client,
								ContactRepositoryContract $contactRepository,
								ContactOptionRepositoryContract $contactOptionsRepository,
								AccountRepositoryContract $accountRepository,
								AddressRepositoryContract $addressRepository)
	{
		plentylog('test')->debug('construct');

		$this->client = $client;
		$this->contactRepository = $contactRepository;
		$this->contactOptionsRepository = $contactOptionsRepository;
		$this->accountRepository = $accountRepository;
		$this->addressRepository = $addressRepository;
	}


	public function run():void
	{
		plentylog('test')->debug('run');
		// TODO: Implement. Do rest call here to get transactions.
		$orders = $this->getOrders();

		plentylog('test')->debug(var_export($orders, true));
	}

	public function getOrders():mixed
	{
		return $this->client->call('findAllShopTransactions', ['shop_id' => 97000509]);
	}
}