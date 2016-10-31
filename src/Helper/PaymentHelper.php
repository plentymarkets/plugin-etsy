<?php

namespace Etsy\Helper;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;

/**
 * Class PaymentHelper
 */
class PaymentHelper
{
	const PLUGIN_KEY = 'EtsyIntegrationPlugin';
	const PAYMENT_KEY = 'PAYMENT_ETSY';

	/**
	 * @var PaymentMethodRepositoryContract
	 */
	private $paymentMethodRepository;
	/**
	 * PrePaymentHelper constructor.
	 *
	 * @param PaymentMethodRepositoryContract $paymentMethodRepository
	 */
	public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository)
	{
		$this->paymentMethodRepository = $paymentMethodRepository;
	}

	/**
	 * @return int|null
	 */
	public function getPaymentMethodId()
	{
		$paymentMethods = $this->paymentMethodRepository->allForPlugin(self::PLUGIN_KEY);

		foreach($paymentMethods as $paymentMethod)
		{
			return $paymentMethod->id;
		}

		return null;
	}
}