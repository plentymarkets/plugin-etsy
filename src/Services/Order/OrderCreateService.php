<?php

namespace Etsy\Services\Order;

use Etsy\Api\Services\PaymentService;
use Etsy\Helper\OrderHelper;
use Etsy\Helper\PaymentHelper;
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
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Plugin\Application;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;

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
     */
    public function create(array $data)
    {
        // create contact
        $contactId = $this->createContact($data);

        // create address
        $addressId = $this->createAddress($contactId, $data);

        // create order
        if (!is_null($addressId)) {
            $order = $this->createOrder($data, $addressId, $contactId);

            // create order comments
            $this->createOrderComments($order->id, $data);

            // create payment
            if ($this->orderHelper->isDirectCheckout((string)$data['payment_method'])) {
                $this->createPayment($data, $order);
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
                'value'  => (string)$this->orderHelper->getPaymentMethodId((string)$data['payment_method']),
            ],

            [
                'typeId' => OrderPropertyType::EXTERNAL_ORDER_ID,
                'value'  => $this->settingsHelper->getShopSettings('shopId') . '_'. $data['receipt_id'],
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
                            'priceOriginalGross' => $transaction['price'],
                            'currency'           => $transaction['currency_code'],
                        ],
                    ],
                    'properties'      => [
                        [
                            'typeId'    => OrderPropertyType::SELLER_ACCOUNT,
                            'subTypeId' => 6,
                            'value'     => (string)$transaction['listing_id'],
                        ],
                        [
                            'typeId'    => 12,
                            'subTypeId' => 6,
                            'value'     => (string)$transaction['transaction_id'],
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
     * @param int   $orderId
     * @param array $data
     */
    private function createOrderComments(int $orderId, array $data)
    {
        try {
            /** @var CommentRepositoryContract $commentRepo */
            $commentRepo = pluginApp(CommentRepositoryContract::class);

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
     * Create payment.
     *
     * @param array $data
     * @param Order $order
     */
    private function createPayment(array $data, Order $order)
    {
        try {
            /** @var PaymentService $paymentService */
            $paymentService = pluginApp(PaymentService::class);

            /** @var PaymentRepositoryContract $paymentRepo */
            $paymentRepo = pluginApp(PaymentRepositoryContract::class);

            $payments = $paymentService->findShopPaymentByReceipt($this->settingsHelper->getShopSettings('shopId'), $data['receipt_id']);

            if (is_array($payments) && count($payments)) {
                /** @var PaymentHelper $paymentHelper */
                $paymentHelper = $this->app->make(PaymentHelper::class);

                foreach ($payments as $paymentData) {
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

                    $this->getLogger(__FUNCTION__)
                         ->addReference('orderId', $order->id)
                         ->addReference('paymentId', $payment->id)
                         ->info('Etsy::order.paymentAssigned', [
                             'amount'            => $payment->amount,
                             'methodOfPaymentId' => $payment->mopId,
                         ]);
                }
            } else {
                $this->getLogger(__FUNCTION__)
                     ->addReference('orderId', $order->id)
                     ->info('Etsy::order.paymentNotFound', [
                         'receiptId' => $data['receipt_id'],
                     ]);
            }
        } catch (\Exception $ex) {
            $this->getLogger(__FUNCTION__)
                 ->addReference('orderId', $order->id)
                 ->error('Etsy::order.paymentError', $ex->getMessage());
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
}
