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

    /** @var array|null $shopsections */
    static $shopsections = null;

    public function __construct(ShopService $shopService, SettingsHelper $settingsHelper)
    {
        $this->shopService = $shopService;
        $this->settingsHelper = $settingsHelper;
        if (!isset(self::$shopsections)) {
            $shopId = $this->settingsHelper->getShopSettings('shopId');
            self::$shopsections = $this->shopService->findAllShopSections($shopId);
        }
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
        $data = [];
        foreach (self::$shopsections['results'] as $shopSection) {
            $data[] = [
                'value' => $shopSection['shop_section_id'],
                'label' => $shopSection['title'],
                'required' => false
            ];
        }

       return $data;
    }
}