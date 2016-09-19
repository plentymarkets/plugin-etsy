<?hh //strict
namespace Etsy\Services\Order;

use Plenty\Plugin\Application;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Address\Models\Address;

use Etsy\Helper\OrderHelper;

class OrderCreateService
{
    /**
    * Application $app
    */
    private Application $app;

    /**
    * ConfigRepository $config
    */
    private ConfigRepository $config;

    /**
    * OrderRepositoryContract $orderRepository
    */
    private OrderRepositoryContract $orderRepository;

    /**
    * AddressRepositoryContract $addressRepository
    */
    private AddressRepositoryContract $addressRepository;

    /**
    * OrderHelper $orderHelper
    */
    private OrderHelper $orderHelper;

    public function __construct(
        Application $app,
        AddressRepositoryContract $addressRepository,
        OrderHelper $orderHelper,
        ConfigRepository $config,
        OrderRepositoryContract $orderRepository
    )
    {
        $this->app = $app;
        $this->addressRepository = $addressRepository;
        $this->orderHelper = $orderHelper;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
    }

    public function create(array<string,mixed> $data):void
    {
        // create contact

        // create address
        $addressId = $this->createAddress($data);

        // create order
        if(!is_null($addressId))
        {
            $this->createOrder($data, $addressId);
        }
    }

    private function createAddress(array<string,mixed> $data):?int
    {
        $addressData = [
            'name2' => $data['name'],
            'address1' => $this->orderHelper->getStreetName((string) $data['first_line']),
            'address2' => $this->orderHelper->getHouseNumber((string) $data['first_line']),
            'postalCode' => $data['zip'],
            'town' => $data['city'],
            'countryId' => $this->orderHelper->getCountryIdByEtsyCountryId((int) $data['country_id']),
        ];

		$addressData['options'] = [
            [
                'typeId' =>	5,
                'value' => $data['buyer_email'],
            ],
        ];

        $address = $this->addressRepository->createAddress($addressData);

        if($address instanceof Address)
        {
            return $address->id;
        }

        return null;
    }

    private function createOrder(array<string,mixed> $data, int $addressId):?int
    {
        $orderData = [
            'typeId' => 1,
            'plentyId' => $this->app->getPlentyId(),
            'statusId' => 3.00,
            'currency' => $data['currency_code'],
        ];

        $orderData['properties'] = [

            // method of payment
            [
                'typeId' => 13,
                'subTypeId' => 1,
                'value' => (string) $this->orderHelper->getPaymentMethodId((string) $data['payment_method']),
            ],

            // external order id
            [
                'typeId' => 14,
                'subTypeId' => 6,
                'value' => (string) $data['order_id'],
            ],
        ];

        $orderData['addresses'] = [
            [
                'typeId' => 1,
                'addressId' => $addressId,
            ],

            [
                'typeId' => 2,
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

    private function getOrderItems(array<string,mixed> $data):array<int,mixed>
    {
        $orderItems = [
            [
                'typeId' => 6,
                'amounts' => [
                    [
                        'priceOriginalGross' => $data['total_shipping_cost'],
                        'currency' => $data['currency_code'],
                    ],
                ],
            ],
        ];

        $transactions = $data['Transactions'];

        if(is_array($transactions))
        {
            foreach($transactions as $transaction)
            {
                $orderItems[] = [
                    'typeId' => 1,
                    'referrerId' => $this->config->get('EtsyIntegrationPlugin.referrerId'),
                    'itemVariationId' => 1, // TODO use variationsku
                    // 'attributeValues' => '', // TODO get this from $transaction['variations']
                    'quantity' => $transaction['quantity'],
                    'orderItemName' => $transaction['title'],
                    'amounts' => [
                        [
                            'priceOriginalGross' => $transaction['price'],
                            'currency' => $transaction['currency_code'],
                        ],
                    ],
                    'properties' => [
                        [
                            'typeId' => 10,
                            'subTypeId' => 6,
                            'value' => (string) $transaction['listing_id'],
                        ],
                        [
                            'typeId' => 12,
                            'subTypeId' => 6,
                            'value' => (string) $transaction['transaction_id'],
                        ],
                    ],
                ];
            }
        }

        return $orderItems;
    }
}
