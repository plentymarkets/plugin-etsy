<?php

namespace Etsy;

use Etsy\DataProviders\EtsyCategoryDataProvider;
use Etsy\DataProviders\EtsyCurrencyDataProvider;
use Etsy\DataProviders\EtsyPropertyDataProvider;
use Etsy\DataProviders\EtsyShippingProfileDataProvider;
use Plenty\Modules\Catalog\Contracts\TemplateContainerContract;
use Plenty\Modules\Catalog\Templates\Template;
use Plenty\Plugin\ServiceProvider;

/**
 * Class CatalogBootServiceProvider
 * @package Plenty\Modules\Catalog
 */
class CatalogBootServiceProvider extends ServiceProvider
{
    /**
     * @param TemplateContainerContract $container
     *
     * @throws \Exception
     */
    public function boot(TemplateContainerContract $container)
    {

        /** @var Template $template */
        $template = $container->register('catalog::etsy.name', 'catalog::etsy.type');

        $template->addMapping([
            'identifier' => 'categories',
            'label' => 'Kategorien',
            'isArray' => true,
            'isMapping' => true,
            'provider' => EtsyCategoryDataProvider::class,
            'mutators' => [
            ]
        ]);

/*
        $template->addMapping([
            'identifier' => 'shippingProfile',
            'label' => 'Versandprofil',
            'isArray' => false,
            'isMapping' => true,
            'provider' => EtsyShippingProfileDataProvider::class,
            'mutators' => [
            ]
        ]);
*/


        $template->addMapping([
            'identifier' => 'etsy_properties',
            'label' => 'Etsy Eigenschaft',
            'isArray' => false,
            'isMapping' => false,
            'provider' => EtsyPropertyDataProvider::class,
            'mutators' => [
            ]
        ]);

        $template->addFilter([
            'name' => 'variationMarket.isVisibleForMarket',
            'params' => [
                ["name" => "marketId", "ref" => "settings.marketId"]
            ]
        ]);

        /*$template->addFilter([
           'name'
        ]);*/

        $template->addSetting([
            'key' => 'marketId',
            'type' => 'market',
            'label' => 'Marktplatz',
            'defaultValue' => 0
        ]);

        $template->setSkuCallback(function ($foo) {
            return 'neue SKU';
        });
    }
}
