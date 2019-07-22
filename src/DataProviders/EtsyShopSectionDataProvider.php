<?php


namespace Etsy\DataProviders;


use Etsy\Api\Services\ShopService;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Catalog\DataProviders\KeyDataProvider;

class EtsyShopSectionDataProvider extends KeyDataProvider
{

    /** @var ShopService  */
    protected $shopService;

    /** @var SettingsHelper  */
    protected $settingsHelper;

    public function __construct(ShopService $shopService, SettingsHelper $settingsHelper)
    {
        $this->shopService = $shopService;
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return "shopSections[]";
    }

    /**
     * @return array
     */
    public function getRows(): array
    {
        $shopId = $this->settingsHelper->getShopSettings('shopId');

        $shopSections = $this->shopService->findAllShopSections($shopId);

        $data = [];
        foreach ($shopSections['results'] as $shopSection) {
            $data[] = [
                'value' => $shopSection['shop_section_id'],
                'label' => $shopSection['title'],
                'required' => false
            ];
        }

       return $data;
    }
}