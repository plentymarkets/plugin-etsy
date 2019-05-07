<?php

namespace Etsy\Migrations;


use Etsy\Helper\SettingsHelper;

class UpdateOldEtsyListings
{
    public function run()
    {
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);

        // todo check if listing is online if yes -> delete if no-> continue
        // todo delete entry in variation_market_status where marketId = Etsy to delete the old etsy sku´s 
    }
}