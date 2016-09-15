<?hh // strict

namespace Etsy\Services\Order;

use Plenty\Exceptions\ValidationException;
use Plenty\Plugin\ConfigRepository;

use Etsy\Api\Client;
use Etsy\Services\Logger;
use Etsy\Services\Order\OrderCreateService;
use Etsy\Validators\EtsyReceiptValidator;

/**
 * Class OrderImportService
 *
 * Gets the orders from Etsy and imports them into plentymarkets.
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
	 * Logger $logger
	 */
	private Logger $logger;

    /**
    * ConfigRepository $config
    */
    private ConfigRepository $config;

    /**
    * OrderCreateService $orderCreateService
    */
    private OrderCreateService $orderCreateService;
	/**
	 * @param Client $client
	 * @param ConfigRepository $config
	 */
	public function __construct(
		Client $client,
        Logger $logger,
        OrderCreateService $orderCreateService,
        ConfigRepository $config
	)
	{
		$this->client = $client;
		$this->logger = $logger;
        $this->orderCreateService = $orderCreateService;
        $this->config = $config;
	}

	/**
	 * Runs the order import process.
	 *
	 * @param string $from
	 * @param string $to
	 */
	public function run(string $from, string $to):void
	{
		//$orders = $this->getOrders($from, $to);

        $receipts = $this->getMockupOrdersResponse($from, $to);

		if(is_array($receipts))
		{
			foreach($receipts as $receiptData)
			{
				try
				{
					EtsyReceiptValidator::validateOrFail($receiptData);

                    $this->orderCreateService->create($receiptData);
				}
				catch(ValidationException $ex)
				{
					$messageBag = $ex->getMessageBag();

                    if(!is_null($messageBag))
                    {
                        $this->logger->log('Can not import order: ...');
                    }
				}
			}
		}
	}

	/**
	 * Gets the orders from Etsy.
	 *
	 * @param string $from
	 * @param string $to
	 * @return array<string, mixed>
	 */
	private function getOrders(string $from, string $to):mixed
	{
		$response = $this->client->call('findAllShopReceipts',
			[
				'shop_id' => $this->config->get('EtsyIntegrationPlugin.shopId'),
				'min_created' => $from,
				'max_created' => $to,
			],
			[],
			[],
			[
				'Transactions' => 'Transactions',
				'Buyer' => 'Buyer',
			]
		);

        if(!is_null($response) && is_array($response['results']))
        {
            return $response['results'];
        }
        else
        {
            return [];
        }
	}

    private function getMockupOrdersResponse(string $from, string $to):mixed
    {
        return [
  [
    "receipt_id"=> 1130492391,
    "receipt_type"=> 0,
    "order_id"=> 454221703,
    "seller_user_id"=> 97266715,
    "buyer_user_id"=> 93057784,
    "creation_tsz"=> 1473935748,
    "last_modified_tsz"=> 1473935748,
    "name"=> "Testman Muster",
    "first_line"=> "Bürgermeister-Brunner-Straße 15",
    "second_line"=> "",
    "city"=> "Kassel",
    "state"=> "Hessen",
    "zip"=> "34117",
    "country_id"=> 91,
    "payment_method"=> "other",
    "payment_email"=> "",
    "message_from_seller"=> null,
    "message_from_buyer"=> "",
    "was_paid"=> false,
    "total_tax_cost"=> "0.00",
    "total_vat_cost"=> "0.00",
    "total_price"=> "0.40",
    "total_shipping_cost"=> "0.10",
    "currency_code"=> "EUR",
    "message_from_payment"=> null,
    "was_shipped"=> false,
    "buyer_email"=> "niklas.grau@plentymarkets.com",
    "seller_email"=> "maurice.kuenzli@plentymarkets.com",
    "discount_amt"=> "0.00",
    "subtotal"=> "0.40",
    "grandtotal"=> "0.50",
    "adjusted_grandtotal"=> "0.50",
    "shipping_tracking_code"=> null,
    "shipping_tracking_url"=> null,
    "shipping_carrier"=> null,
    "shipping_note"=> null,
    "shipping_notification_date"=> null,
    "shipments"=> [],
    "has_local_delivery"=> false,
    "shipping_details"=> [
      "can_mark_as_shipped"=> false,
      "was_shipped"=> false,
      "is_future_shipment"=> true,
      "not_shipped_state_display"=> "Not Shipped",
      "shipping_method"=> "Standard Shipping"
  ],
    "transparent_price_message"=> "VAT  where applicable",
    "show_channel_badge"=> false,
    "channel_badge_suffix_string"=> "on Create",
    "Transactions"=> [
      [
        "transaction_id"=> 1188203113,
        "title"=> "2 Selbstgemachtes Radio Box using Raspberry Pi",
        "description"=> "Das ist ein Radio das gar nicht funktioniert und gar nichts macht. Wir wollen einfach Listings auf etsy testen. Nur so.",
        "seller_user_id"=> 97266715,
        "buyer_user_id"=> 93057784,
        "creation_tsz"=> 1473935748,
        "paid_tsz"=> null,
        "shipped_tsz"=> null,
        "price"=> "0.20",
        "currency_code"=> "EUR",
        "quantity"=> 1,
        "tags"=> [
          "Supplies"
        ],
        "materials"=> [],
        "image_listing_id"=> 1090352341,
        "receipt_id"=> 1130492391,
        "shipping_cost"=> "0.00",
        "is_digital"=> false,
        "file_data"=> "",
        "listing_id"=> 478985761,
        "is_quick_sale"=> false,
        "seller_feedback_id"=> null,
        "buyer_feedback_id"=> null,
        "transaction_type"=> "listing",
        "url"=> "https=>//www.etsy.com/transaction/1188203113",
        "variations"=> []
    ],
      [
        "transaction_id"=> 1188203111,
        "title"=> "2 Glas mit Loch, oben und unten",
        "description"=> "Das sit gar nicht benutzbar. Das hat ein großes Loch. Wasser fließt einfach raus.",
        "seller_user_id"=> 97266715,
        "buyer_user_id"=> 93057784,
        "creation_tsz"=> 1473935748,
        "paid_tsz"=> null,
        "shipped_tsz"=> null,
        "price"=> "0.20",
        "currency_code"=> "EUR",
        "quantity"=> 1,
        "tags"=> [
          "Housewares"
        ],
        "materials"=> [],
        "image_listing_id"=> 1043801250,
        "receipt_id"=> 1130492391,
        "shipping_cost"=> "0.00",
        "is_digital"=> false,
        "file_data"=> "",
        "listing_id"=> 478985813,
        "is_quick_sale"=> false,
        "seller_feedback_id"=> null,
        "buyer_feedback_id"=> null,
        "transaction_type"=> "listing",
        "url"=> "https=>//www.etsy.com/transaction/1188203111",
        "variations"=> []
      ]
    ],
    "Buyer"=> [
      "user_id"=> 93057784,
      "login_name"=> "plentytestkauf",
      "creation_tsz"=> 1473151561,
      "user_pub_key"=> [
        "key"=> "-----BEGIN PUBLIC KEY-----\nMIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAwpiQNlsniZ4u7ynBS3pp\nHrpkfAQ2+A2tKWzmKf63YjghTvd2Zk+tf2apOp8dvT002mv4yJcVOGjx8bBPGvGa\ns5u132f8y+Dt/UPJsL1WDLoYgrxZWupNRwvDhyog9d3OD7B/4eL3YyjyghmhRS+E\nlE1JB5f3iE0opVGsBp4xPAt4TSuvPsR9hAPOarXgWv2nf8bZTjkEwsIwI7IXTgSl\nmg5XNUO+8DRnuu+bAenfdlKy/N9ZdPRNz4Bmlj4txCRDa2T0AKMtxtj7CtMI9SLe\ncOG17Ge9C/JLM4K2izFreWsLVZFefFOdjw7P8ZOUsMT0OJ9kZeUXYc6T5ZYW76hN\n7SB17RSTVhrdcHdsZzcu7cskfV3VNzRJQ9KogoKxPLjEWe9dDqbCMI2DrazjuIRb\nmyeLH98Iz/Mg9FRlr/KKYQeH/iJdM1DqX5k/bRcjaUuHoEgVYah03JqVkzby+PrT\ng/iMCf4Ms3Mmzd28Yi9qsiyrtIci5KrFsiWFxMR5jwudAHEnfE/V/g9xEbl4gvvz\nsXVtdPxLi13h7/Mltsl45MfeTwkUZqqTZbf/+QZcqTcNRfcikhJGj9rS0rmSKYKK\nPH5RbOrrJ5J8OkHp2uSGA82UrkDsoq4k0kXCNmukgpQ1zzDQHfbeEh1vTtgF3JMb\nwBO+/JMpZBfbuzZ2MjxZlYsCAwEAAQ==\n-----END PUBLIC KEY-----\n",
        "key_id"=> 48665448274
    ],
      "referred_by_user_id"=> null,
      "feedback_info"=> [
        "count"=> 0,
        "score"=> null
      ]
    ]
],
  [
    "receipt_id"=> 1127572778,
    "receipt_type"=> 0,
    "order_id"=> 454220983,
    "seller_user_id"=> 97266715,
    "buyer_user_id"=> 93057784,
    "creation_tsz"=> 1473935006,
    "last_modified_tsz"=> 1473935006,
    "name"=> "Testman Muster",
    "first_line"=> "Bürgermeister-Brunner-Straße 15",
    "second_line"=> "",
    "city"=> "Kassel",
    "state"=> "Hessen",
    "zip"=> "34117",
    "country_id"=> 91,
    "payment_method"=> "other",
    "payment_email"=> "",
    "message_from_seller"=> null,
    "message_from_buyer"=> "Ich möchte diese zwei Produkte umbedingt haben!",
    "was_paid"=> false,
    "total_tax_cost"=> "0.00",
    "total_vat_cost"=> "0.00",
    "total_price"=> "0.60",
    "total_shipping_cost"=> "0.00",
    "currency_code"=> "EUR",
    "message_from_payment"=> null,
    "was_shipped"=> false,
    "buyer_email"=> "niklas.grau@plentymarkets.com",
    "seller_email"=> "maurice.kuenzli@plentymarkets.com",
    "discount_amt"=> "0.00",
    "subtotal"=> "0.60",
    "grandtotal"=> "0.60",
    "adjusted_grandtotal"=> "0.60",
    "shipping_tracking_code"=> null,
    "shipping_tracking_url"=> null,
    "shipping_carrier"=> null,
    "shipping_note"=> null,
    "shipping_notification_date"=> null,
    "shipments"=> [],
    "has_local_delivery"=> false,
    "shipping_details"=> [
      "can_mark_as_shipped"=> false,
      "was_shipped"=> false,
      "is_future_shipment"=> true,
      "not_shipped_state_display"=> "Not Shipped",
      "shipping_method"=> "Standard Shipping"
  ],
    "transparent_price_message"=> "VAT  where applicable",
    "show_channel_badge"=> false,
    "channel_badge_suffix_string"=> "on Create",
    "Transactions"=> [
     [
        "transaction_id"=> 1188202029,
        "title"=> "Selbstgemachtes Radio Box using Raspberry Pi",
        "description"=> "Das ist ein Radio das gar nicht funktioniert und gar nichts macht. Wir wollen einfach Listings auf etsy testen. Nur so.",
        "seller_user_id"=> 97266715,
        "buyer_user_id"=> 93057784,
        "creation_tsz"=> 1473935006,
        "paid_tsz"=> null,
        "shipped_tsz"=> null,
        "price"=> "0.20",
        "currency_code"=> "EUR",
        "quantity"=> 1,
        "tags"=> [
          "Supplies"
        ],
        "materials"=> [],
        "image_listing_id"=> 1090352341,
        "receipt_id"=> 1127572778,
        "shipping_cost"=> "0.00",
        "is_digital"=> false,
        "file_data"=> "",
        "listing_id"=> 478984219,
        "is_quick_sale"=> false,
        "seller_feedback_id"=> null,
        "buyer_feedback_id"=> null,
        "transaction_type"=> "listing",
        "url"=> "https://www.etsy.com/transaction/1188202029",
        "variations"=> []
    ],
      [
        "transaction_id"=> 1188202027,
        "title"=> "Glas mit Loch, oben und unten",
        "description"=> "Das sit gar nicht benutzbar. Das hat ein großes Loch. Wasser fließt einfach raus.",
        "seller_user_id"=> 97266715,
        "buyer_user_id"=> 93057784,
        "creation_tsz"=> 1473935006,
        "paid_tsz"=> null,
        "shipped_tsz"=> null,
        "price"=> "0.20",
        "currency_code"=> "EUR",
        "quantity"=> 2,
        "tags"=> [
          "Housewares"
        ],
        "materials"=> [],
        "image_listing_id"=> 1043801250,
        "receipt_id"=> 1127572778,
        "shipping_cost"=> "0.00",
        "is_digital"=> false,
        "file_data"=> "",
        "listing_id"=> 478984579,
        "is_quick_sale"=> false,
        "seller_feedback_id"=> null,
        "buyer_feedback_id"=> null,
        "transaction_type"=> "listing",
        "url"=> "https://www.etsy.com/transaction/1188202027",
        "variations"=> []
      ]
    ],
    "Buyer"=> [
      "user_id"=> 93057784,
      "login_name"=> "plentytestkauf",
      "creation_tsz"=> 1473151561,
      "user_pub_key"=> [
        "key"=> "-----BEGIN PUBLIC KEY-----\nMIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAwpiQNlsniZ4u7ynBS3pp\nHrpkfAQ2+A2tKWzmKf63YjghTvd2Zk+tf2apOp8dvT002mv4yJcVOGjx8bBPGvGa\ns5u132f8y+Dt/UPJsL1WDLoYgrxZWupNRwvDhyog9d3OD7B/4eL3YyjyghmhRS+E\nlE1JB5f3iE0opVGsBp4xPAt4TSuvPsR9hAPOarXgWv2nf8bZTjkEwsIwI7IXTgSl\nmg5XNUO+8DRnuu+bAenfdlKy/N9ZdPRNz4Bmlj4txCRDa2T0AKMtxtj7CtMI9SLe\ncOG17Ge9C/JLM4K2izFreWsLVZFefFOdjw7P8ZOUsMT0OJ9kZeUXYc6T5ZYW76hN\n7SB17RSTVhrdcHdsZzcu7cskfV3VNzRJQ9KogoKxPLjEWe9dDqbCMI2DrazjuIRb\nmyeLH98Iz/Mg9FRlr/KKYQeH/iJdM1DqX5k/bRcjaUuHoEgVYah03JqVkzby+PrT\ng/iMCf4Ms3Mmzd28Yi9qsiyrtIci5KrFsiWFxMR5jwudAHEnfE/V/g9xEbl4gvvz\nsXVtdPxLi13h7/Mltsl45MfeTwkUZqqTZbf/+QZcqTcNRfcikhJGj9rS0rmSKYKK\nPH5RbOrrJ5J8OkHp2uSGA82UrkDsoq4k0kXCNmukgpQ1zzDQHfbeEh1vTtgF3JMb\nwBO+/JMpZBfbuzZ2MjxZlYsCAwEAAQ==\n-----END PUBLIC KEY-----\n",
        "key_id"=> 48665448274
    ],
      "referred_by_user_id"=> null,
      "feedback_info"=> [
        "count"=> 0,
        "score"=> null
      ]
    ]
  ]
];
    }
}
