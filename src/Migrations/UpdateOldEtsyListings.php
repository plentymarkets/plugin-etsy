<?php

namespace Etsy\Migrations;


use Etsy\Helper\SettingsHelper;

class UpdateOldEtsyListings
{
    public function run()
    {
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);
    }
}