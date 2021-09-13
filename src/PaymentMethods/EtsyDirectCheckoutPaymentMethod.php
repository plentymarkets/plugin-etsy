<?php

namespace Etsy\PaymentMethods;

use Etsy\Helper\AccountHelper;
use Plenty\Modules\Payment\Method\Services\PaymentMethodBaseService;

class EtsyDirectCheckoutPaymentMethod extends PaymentMethodBaseService
{
    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return false;
    }

    /**
     * @param string $lang
     * @return string
     */
    public function getName(string $lang = 'de'): string
    {
        return 'Etsy Direct Checkout';
    }

    /**
     * @param string $lang
     * @return string
     */
    public function getBackendName(string $lang = 'de'): string
    {
        return $this->getName($lang);
    }

    /**
     * @return string
     */
    public function getBackendIcon(): string
    {
        return parent::getBackendIcon();
    }

    /**
     * @return bool
     */
    public function isBackendSearchable(): bool
    {
        /** @var AccountHelper $accountHelper */
        $accountHelper = pluginApp(AccountHelper::class);
        if ($accountHelper->isValidConfig()) {
            return true;
        }

        return false;
    }
}