<?php

namespace Etsy\Services\Shop;

use Etsy\Api\Services\ShopService;
use Etsy\Helper\SettingsHelper;

/**
 * Class ShopImportService
 */
class ShopImportService
{
	/**
	 * @return void
	 */
	public function run()
	{
		try
		{
			/** @var ShopService $shopService */
			$shopService = pluginApp(ShopService::class);

			$response = $shopService->findAllUserShops('__SELF__');

			$shopList = [];

			if($response && isset($response['results']) && count($response['results']))
			{
				foreach($response['results'] as $shopData)
				{
					$shopList[$shopData['shop_id']] = [
						'shopId' => $shopData['shop_id'],
						'shopName' => $shopData['shop_name'],
						'url' => $shopData['url'],
                        'currency_code' => $shopData['currency_code']
					];
				}
			}

			pluginApp(SettingsHelper::class)->save(SettingsHelper::SETTINGS_ETSY_SHOPS, (string) json_encode($shopList));
		}
		catch(\Exception $ex)
		{
			// TODO log
		}

	}
}
