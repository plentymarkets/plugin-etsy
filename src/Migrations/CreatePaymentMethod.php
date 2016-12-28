<?php

namespace Etsy\Migrations;

use Etsy\Helper\PaymentHelper;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;

/**
 * Class CreatePaymentMethod
 */
class CreatePaymentMethod
{
	/**
	 * @var PaymentMethodRepositoryContract $paymentMethodRepository
	 */
	private $paymentMethodRepository;

	/**
	 * @var PaymentHelper
	 */
	private $paymentHelper;

	/**
	 * @param PaymentMethodRepositoryContract $paymentMethodRepository
	 * @param PaymentHelper                   $paymentHelper
	 */
	public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository, PaymentHelper $paymentHelper)
	{
		$this->paymentMethodRepository = $paymentMethodRepository;
		$this->paymentHelper           = $paymentHelper;
	}

	/**
	 * @return void
	 */
	public function run()
	{
		if(is_null($this->paymentHelper->getPaymentMethodId()))
		{
			$paymentMethodData = [
				'pluginKey'  => PaymentHelper::PLUGIN_KEY,
				'paymentKey' => PaymentHelper::PAYMENT_KEY,
				'name'       => 'Etsy Direct Checkout',
			];

			$this->paymentMethodRepository->createPaymentMethod($paymentMethodData);
		}
	}
}