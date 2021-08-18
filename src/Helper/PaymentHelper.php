<?php

namespace Etsy\Helper;

use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;

/**
 * Class PaymentHelper
 */
class PaymentHelper
{
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
     * @param string $paymentMethod
     * @return int|null
     */
	public function getPaymentMethodId(string $paymentMethod = 'cc')
	{
	    switch ($paymentMethod) {
            case 'other':
                $paymentMethod = $this->paymentMethodRepository->findByPluginAndPaymentKey('plenty', 'PREPAYMENT');
                if (is_null($paymentMethod)) {
                    return PLENTY_MOP_PREPAYMENT;
                }

                return $paymentMethod->id;

            case 'pp':
                $paymentMethod = $this->paymentMethodRepository->findByPluginAndPaymentKey('plentyPayPal', 'PAYPAL');
                if (is_null($paymentMethod)) {
                    return PLENTY_MOP_PAYPALEXPRESS;
                }

                return $paymentMethod->id;

            case 'cc':
                $paymentMethod = $this->paymentMethodRepository->findByPluginAndPaymentKey(SettingsHelper::PLUGIN_NAME, self::PAYMENT_KEY);
                if (is_null($paymentMethod)) {
                    return null;
                }

                return $paymentMethod->id;

            case 'ck':
            case 'mo':
                $paymentMethod = $this->paymentMethodRepository->findByPluginAndPaymentKey('plenty', 'COD');
                if (is_null($paymentMethod)) {
                    return PLENTY_MOP_COD;
                }

                return $paymentMethod->id;
        }
        return null;
	}
}