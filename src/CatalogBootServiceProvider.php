<?php

namespace Etsy;

use Etsy\DataProviders\EtsyCategoryDataProvider;
use Etsy\DataProviders\EtsyPropertyDataProvider;
use Etsy\DataProviders\EtsyShippingProfileDataProvider;
use Plenty\Modules\Catalog\Contracts\TemplateContainerContract;
use Plenty\Modules\Catalog\Templates\Template;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Translation\Translator;

/**
 * Class CatalogBootServiceProvider
 * @package Plenty\Modules\Catalog
 */
class CatalogBootServiceProvider extends ServiceProvider
{

    /**
     * @param TemplateContainerContract $container
     *
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function boot(TemplateContainerContract $container)
    {

        /** @var Template $template */
        $template = $container->register('Etsy::catalog.name', 'Etsy::catalog.type');

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
            'identifier' => 'shipping_profile',
            'label' => 'Versandprofile',
            'isArray' => true,
            'isMapping' => true,
            'provider' => EtsyShippingProfileDataProvider::class,
            'mutators' => [
            ]
        ]);

        $template->addMapping([
            'identifier' => 'etsy_properties',
            'label' => 'Eigenschaften',
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

        $template->addSetting([
            'key' => 'marketId',
            'type' => 'market',
            'label' => 'Marketplace',
            'defaultValue' => 0
        ]);
    }
}
