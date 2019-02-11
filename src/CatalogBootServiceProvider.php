<?php

namespace Etsy;

use Etsy\DataProviders\EtsyCategoryDataProvider;
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
            'identifier' => 'etsy_properties',
            'label' => 'Etsy Eigenschaften',
            'isArray' => false,
            'isMapping' => false,
            'provider' => EtsyPropertyDataProvider::class,
            'mutators' => [
            ]
        ]);

        $template->addMapping([
            'identifier' => 'categories',
            'label' => 'Kategorien',
            'isArray' => true,
            'isMapping' => true,
            'provider' => EtsyCategoryDataProvider::class,
            'mutators' => [
            ]
        ]);

        $template->addMapping([
            'identifier' => 'shippingProfile',
            'label' => 'Versandprofil',
            'isArray' => false,
            'isMapping' => true,
            'provider' => EtsyShippingProfileDataProvider::class,
            'mutators' => [
            ]
        ]);

        //todo: Währung

        //todo: Preis

        $template->addFilter([
            'name' => 'variationBase.isActive',
        ]);

        $template->addFilter([
            'name' => 'variationMarket.isVisibleForMarket',
            'params' => [
                ["name" => "marketId", "value" => "settings.marketId"]
            ]
        ]);

        /*$template->addFilter([
           'name'
        ]);*/

        $template->addSetting([
            'key' => 'settings.marketId',
            'type' => 'market',
            'label' => 'Marktplatz',
            'defaultValue' => 0
        ]);

        $template->addSetting([
            'key' => 'settings.foo',
            'type' => 'foo',
            'label' => 'Foo',
            'defaultValue' => 0
        ]);

        $template->addSetting([
            'key' => 'settings.bar',
            'type' => 'bar',
            'label' => 'Bar',
            'defaultValue' => 0
        ]);

        $template->setSkuCallback(function ($foo) {
            return 'neue SKU';
        });
    }
}
