<?php

namespace Etsy\DataProviders;

use Etsy\Api\Services\ShippingTemplateService;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Catalog\DataProviders\KeyDataProvider;

class EtsyShippingProfileDataProvider extends KeyDataProvider
{
    /**
     * @var $settingsHelper
     */
    protected $settingsHelper;

    /**
     * @var $shippingTemplateService
     */
    protected $shippingTemplateService;

    public function __construct(SettingsHelper $settingsHelper, ShippingTemplateService $shippingTemplateService)
    {
        $this->settingsHelper = $settingsHelper;
        $this->shippingTemplateService = $shippingTemplateService;
    }

    public function getKey(): string
    {
       return 'shipping_profiles[]';
    }

    public function getRows(): array
    {
        $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');

        $shippingProfiles = $this->shippingTemplateService->findAllUserShippingProfiles('__SELF__', $language);
        $data = [];

        foreach ($shippingProfiles as $key => $shippingProfile) {
            $data[] = [
                'value' => $shippingProfile['shipping_template_id'],
                'label' => $shippingProfile['title'],
                'required' => false
            ];
        }

        return $data;
    }
}