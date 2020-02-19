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

    /** @var array|null $shippingProfiles */
    static $shippingProfiles = null;

    public function __construct(SettingsHelper $settingsHelper, ShippingTemplateService $shippingTemplateService)
    {
        $this->settingsHelper = $settingsHelper;
        $this->shippingTemplateService = $shippingTemplateService;

        if (!isset(self::$shippingProfiles)) {
            $language = $this->settingsHelper->getShopSettings('mainLanguage', 'de');
            self::$shippingProfiles = $this->shippingTemplateService->findAllUserShippingProfiles('__SELF__', $language);
        }
    }

    public function getKey(): string
    {
       return 'shipping_profiles[]';
    }

    public function getRows(): array
    {
        $data = [];

        foreach (self::$shippingProfiles as $key => $shippingProfile) {
            $data[] = [
                'value' => $shippingProfile['shipping_template_id'],
                'label' => $shippingProfile['title'],
                'required' => false
            ];
        }

        return $data;
    }
}