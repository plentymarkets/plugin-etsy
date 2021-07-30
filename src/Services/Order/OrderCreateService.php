<?php

namespace Etsy\Services\Order;

use Etsy\Helper\OrderHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Account\Address\Contracts\AddressContactRelationRepositoryContract;
use Plenty\Modules\Account\Address\Models\AddressRelationType;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Accounting\Contracts\AccountingServiceContract;
use Plenty\Modules\Accounting\Vat\Contracts\VatInitContract;
use Plenty\Modules\Comment\Contracts\CommentRepositoryContract;
use Plenty\Modules\Comment\Models\Comment;
use Plenty\Modules\Item\Variation\Models\Variation;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Models\OrderItemType;
use Plenty\Modules\Order\Property\Contracts\OrderItemPropertyRepositoryContract;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Plugin\Application;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;

/**
 * Class OrderCreateService
 */
class OrderCreateService
{
	use Loggable;

    private const PERSONALIZATION_NAMING = [
        'Personalisierung'
    ];

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
	 * @var Translator
	 */
	private $translator;

	/**
	 * @param Application    $app
	 * @param OrderHelper    $orderHelper
	 * @param SettingsHelper $settingsHelper
	 * @param Translator     $translator
	 */
	public function __construct(Application $app,
								OrderHelper $orderHelper,
								SettingsHelper $settingsHelper,
								Translator $translator)
	{
		$this->app            = $app;
		$this->orderHelper    = $orderHelper;
		$this->settingsHelper = $settingsHelper;
		$this->translator     = $translator;
	}

	/**
	 * @param array $data
	 * @param string $lang
	 */
	public function create(array $data, $lang = null)
	{
		// create contact
		$contactId = $this->createContact($data);

		// create address
		$addressId = $this->createAddress($contactId, $data);

		// create order
		if (!is_null($addressId)) {
			$order = $this->createOrder($data, $addressId, $contactId, $lang);

			// create order comments
			$this->createOrderComments($order, $data);

			// create payment
            if ($this->orderHelper->isDirectCheckout((string)$data['payment_method']) &&
                $data['was_paid'] == true) {
                $this->orderHelper->createPayment($data, $order);
            } else {
                $this->getLogger(__FUNCTION__)
                    ->addReference('orderId', $order->id)
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

		if (isset($data['buyer_email']) && strlen($data['buyer_email'])) {
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

		$this->getLogger(__FUNCTION__)
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
			'address1'                 => $this->orderHelper->getStreetName((string)$data['first_line']),
			'address2'                 => $this->orderHelper->getHouseNumber((string)$data['first_line']),
			'postalCode'               => $data['zip'],
			'town'                     => $data['city'],
			'countryId'                => $this->orderHelper->getCountryIdByEtsyCountryId((int)$data['country_id']),
			'useAddressLightValidator' => true,
		];

		if (isset($addressData['countryId']) && $addressData['countryId'] > 0 && isset($data['state']) && strlen($data['state'])) {
			$addressData['stateId'] = $this->orderHelper->getStateIdByCountryIdAndIsoCode($addressData['countryId'], $data['state']);
		}

		if (isset($data['second_line']) && strlen($data['second_line'])) {
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

		$address = $contactAddressRepo->createAddress($addressData, $contactId, AddressRelationType::DELIVERY_ADDRESS);

		$this->getLogger(__FUNCTION__)
			->addReference('etsyReceiptId', $data['receipt_id'])
			->addReference('contactId', $contactId)
			->addReference('addressId', $address->id)
			->info('Etsy::order.addressCreated');

		// add address relation for typeId AddressRelationType::BILLING_ADDRESS
		/** @var AddressContactRelationRepositoryContract $addressContactRelationRepo */
		$addressContactRelationRepo = pluginApp(AddressContactRelationRepositoryContract::class);

		$addressContactRelationRepo->createAddressContactRelation([
			[
				'contactId' => $contactId,
				'addressId' => $address->id,
				'typeId' => AddressRelationType::BILLING_ADDRESS,
			]
		]);

		return $address->id;
	}

	/**
	 * @param array  $data
	 * @param int    $addressId
	 * @param int    $contactId
	 * @param string $lang
	 * @return Order
	 * @throws \Plenty\Exceptions\ValidationException
	 */
	private function createOrder(array $data, $addressId, $contactId, $lang = null): Order
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
				'value'  => (string)$this->orderHelper->getPaymentMethodId((string)$data['payment_method']),
			],

			[
				'typeId' => OrderPropertyType::EXTERNAL_ORDER_ID,
				'value'  => $this->settingsHelper->getShopSettings('shopId') . '_'. $data['receipt_id'],
			],
		];

		if (is_string($lang) && strlen($lang)){
			array_push($orderData['properties'], [
				'typeId' => OrderPropertyType::DOCUMENT_LANGUAGE,
				'value' => $lang,
			]);
		}

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

		$this->getLogger(__FUNCTION__)
			->addReference('etsyReceiptId', $data['receipt_id'])
			->addReference('contactId', $contactId)
			->addReference('addressId', $addressId)
			->addReference('orderId', $order->id)
			->report('Etsy::order.orderCreated');

		return $order;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	private function getOrderItems(array $data)
	{
		$orderItems  = [];
		$minVatField = null;
		$vatInit     = $this->getVatInit($data);
		$countryVat  = $vatInit->getUsingVat();

		$transactions = $data['Transactions'];

		$taxPerTransaction = $this->getTaxInformation($transactions, $data);

		if (is_array($transactions)) {
			foreach ($transactions as $transaction) {
				$itemVariationId = $this->matchVariationId($transaction['product_data']['sku']);
				$variation       = $this->getVariationById($itemVariationId);

				if (!$variation) {
					$vatId = 0;
				} elseif ($variation->vatId) {
					$vatId = $variation->vatId;
				} else {
					$vatId = $variation->parent->vatId;
				}

				if ($this->orderHelper->isDirectCheckout((string)$data['payment_method'])) {
					$price = $transaction['price'];
				} else {
					$tax = $taxPerTransaction / $transaction['quantity'];
					$price = $transaction['price'] + $tax;
				}

				$orderItems[] = [
					'typeId'          => $itemVariationId > 0 ? 1 : 9,
					'referrerId'      => $this->orderHelper->getReferrerId(),
					'itemVariationId' => $itemVariationId,
					'quantity'        => $transaction['quantity'],
					'orderItemName'   => html_entity_decode($transaction['title']),
					'countryVatId'    => $countryVat->id,
					'vatField'        => $vatId,
					'amounts'         => [
						[
							'priceOriginalGross' => $price,
							'currency'           => $transaction['currency_code'],
						],
					],
					'properties'      => [
						[
							'typeId'    => OrderPropertyType::EXTERNAL_ITEM_ID,
							'subTypeId' => 6,
							'value'     => (string)$transaction['listing_id'],
						],
					],
				];

				if (!$minVatField) {
					$minVatField = $vatId;
				}

				$minVatField = min($minVatField, $vatId);
			}

			if (count($orderItems) > 0) {
				// add coupon item position
				if (isset($data['discount_amt']) && $data['discount_amt'] > 0) {
					$orderItems[] = [
						'typeId'        => OrderItemType::TYPE_PROMOTIONAL_COUPON,
						'referrerId'    => $this->orderHelper->getReferrerId(),
						'quantity'      => 1,
						'orderItemName' => 'Coupon',
						'countryVatId'  => $countryVat->id,
						'vatField'      => $minVatField ? $minVatField : 0,
						'amounts'       => [
							[
								'priceOriginalGross' => -$data['discount_amt'],
								'currency'           => $data['currency_code'],
							],
						],
					];
				}

				// add wrapping item position
				if (isset($data['is_gift']) && $data['is_gift'] === true) {
					$orderItems[] = [
						'typeId'        => OrderItemType::TYPE_UNASSIGEND_VARIATION,
						'referrerId'    => $this->orderHelper->getReferrerId(),
						'quantity'      => 1,
						'orderItemName' => $this->getWrapTitle($data),
						'countryVatId'  => $countryVat->id,
						'vatField'      => $minVatField ? $minVatField : 0,
						'amounts'       => [
							[
								'priceOriginalGross' => 0,
								'currency'           => $data['currency_code'],
							],
						],
					];
				}

				$orderItems[] = [
					'typeId'          => 6,
					'itemVariationId' => 0,
					'quantity'        => 1,
					'orderItemName'   => 'Shipping Costs',
					'countryVatId'    => $countryVat->id,
					'vatField'        => $minVatField ? $minVatField : 0,
					'amounts'         => [
						[
							'priceOriginalGross' => $data['total_shipping_cost'],
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

		foreach ($result as $variationSku) {
			return $variationSku->variationId;
		}

		return 0;
	}

	/**
	 * Get the variation.
	 *
	 * @param int $variationId
	 *
	 * @return null|Variation
	 */
	private function getVariationById(int $variationId)
	{
		try {
			/** @var \Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract $variationContract */
			$variationContract = pluginApp(\Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract::class);

			/** @var \Plenty\Modules\Item\Variation\Models\Variation $variation */
			$variation = $variationContract->findById($variationId);

			if ($variation instanceof Variation) {
				return $variation;
			}
		} catch (\Throwable $e) {
		}

		return null;
	}

	/**
	 * Create order comments.
	 *
	 * @param Order $order
	 * @param array $data
	 */
	private function createOrderComments(Order $order, array $data)
	{
	    $orderId = $order->id;

		try {
			/** @var CommentRepositoryContract $commentRepo */
			$commentRepo = pluginApp(CommentRepositoryContract::class);

            //save personalization information
            foreach ($data['Transactions'] as $transaction) {
                if (isset($transaction['variations'])) {
                    foreach ($transaction['variations'] as $attribute) {
                        if (in_array($attribute['formatted_name'], self::PERSONALIZATION_NAMING)) {
                            foreach ($order->orderItems as $orderItem) {
                                $orderItemExternalItemId = $orderItem->property(OrderPropertyType::EXTERNAL_ITEM_ID);

                                if (is_null($orderItemExternalItemId)) {
                                    continue;
                                }

                                if ($orderItemExternalItemId == (string)$transaction['listing_id']) {
                                    $comment = [
                                        'referenceType'       => Comment::REFERENCE_TYPE_ORDER,
                                        'referenceValue'      => $orderId,
                                        'isVisibleForContact' => true,
                                        'text'                => '<b>' . $this->translator->trans('Etsy::order.personalizationMessage') . $orderItem->itemVariationId . ':</b><br><br>' . nl2br(html_entity_decode($attribute['formatted_value']))
                                    ];
                                    $commentRepo->createComment($comment);
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }

			// save buyer message
			if (isset($data['message_from_buyer']) && strlen($data['message_from_buyer'])) {
				$commentRepo->createComment([
					'referenceType'       => Comment::REFERENCE_TYPE_ORDER,
					'referenceValue'      => $orderId,
					'isVisibleForContact' => true,
					'text'                => nl2br(html_entity_decode($data['message_from_buyer']))
				]);
			}

			// save payment message
			if (isset($data['message_from_payment']) && strlen($data['message_from_payment'])) {
				$commentRepo->createComment([
					'referenceType'       => Comment::REFERENCE_TYPE_ORDER,
					'referenceValue'      => $orderId,
					'isVisibleForContact' => true,
					'text'                => '<b>' . $this->translator->trans('Etsy::order.paymentMessage') . ':</b><br><br>' . nl2br(html_entity_decode($data['message_from_buyer']))
				]);
			}

			// save gift message
			if (isset($data['gift_message']) && strlen($data['gift_message'])) {
				$commentRepo->createComment([
					'referenceType'       => Comment::REFERENCE_TYPE_ORDER,
					'referenceValue'      => $orderId,
					'isVisibleForContact' => true,
					'text'                => '<b>' . $this->translator->trans('Etsy::order.giftMessage') . ':</b><br><br>' . nl2br(html_entity_decode($data['gift_message']))
				]);
			}
		} catch (\Exception $ex) {
			$this->getLogger(__FUNCTION__)
				->addReference('orderId', $orderId)
				->error('Etsy::order.commentsError', $ex->getMessage());
		}
	}

	/**
	 * Get the VAT configuration.
	 *
	 * @param array $data
	 *
	 * @return VatInitContract
	 */
	private function getVatInit(array $data): VatInitContract
	{
		/** @var VatInitContract $vatInit */
		$vatInit = pluginApp(VatInitContract::class);

		/** @var AccountingServiceContract $accountingService */
		$accountingService = pluginApp(AccountingServiceContract::class);

		$vatInit->init($this->orderHelper->getCountryIdByEtsyCountryId((int)$data['country_id']), '', $accountingService->detectLocationId($this->app->getPlentyId()), $this->orderHelper->getCountryIdByEtsyCountryId((int)$data['country_id']));

		return $vatInit;
	}

	/**
	 * Get wrap title
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	private function getWrapTitle(array $data)
	{
		if (isset($data['needs_gift_wrap']) && $data['needs_gift_wrap'] === true) {
			$title = $this->translator->trans('Etsy::order.giftWrapWithPaper');
		} else {
			$title = $this->translator->trans('Etsy::order.giftWrapWithoutPaper');
		}

		if (isset($data['gift_message']) && strlen($data['gift_message'])) {
			$title .= ' ' . $this->translator->trans('Etsy::order.giftTitleMessage', ['message' => $data['gift_message']]);
		}

		return $title;
	}

	/**
	 * Get tax amount for one transaction based on the total tax amount and total transactions
	 *
	 * @param array $transactions
	 * @param       $data
	 * @return float|int
	 */
	private function getTaxInformation(array $transactions, $data)
	{
		$transactionAmount = count($transactions);

		if (isset($data['total_tax_cost']) && $data['total_tax_cost'] != null) {
			$totalTax = $data['total_tax_cost'];
			$taxPerTransaction = $totalTax / $transactionAmount;

			return $taxPerTransaction;
		}
		else {
			return 0.0;
		}
	}
}