<?php

namespace Etsy;

use Etsy\DataProviders\EtsyCategoryDataProvider;
use Etsy\DataProviders\EtsyDescriptionDataProvider;
use Etsy\DataProviders\EtsyPropertyDataProvider;
use Etsy\DataProviders\EtsySalesPriceDataProvider;
use Etsy\DataProviders\EtsyShippingProfileDataProvider;
use Etsy\DataProviders\EtsyShopSectionDataProvider;
use Etsy\DataProviders\EtsyTagsDataProvider;
use Etsy\DataProviders\EtsyTitleDataProvider;
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
     * @throws \Exception
     */
    public function boot(TemplateContainerContract $container)
    {

        /** @var Template $template */
        $template = $container->register('Etsy::catalog.name', 'Etsy::catalog.type');

        $template->addMapping([
            'identifier' => 'title',
            'label' => 'Titel',
            'isArray' => true,
            'isMapping' => false,
            'provider' => EtsyTitleDataProvider::class,
            'mutators' => [
            ]
        ]);

        $template->addMapping([
            'identifier' => 'description',
            'label' => 'Beschreibung',
            'isArray' => true,
            'isMapping' => false,
            'provider' => EtsyDescriptionDataProvider::class,
            'mutators' => [
            ]
        ]);

        $template->addMapping([
            'identifier' => 'tags',
            'label' => 'Tags',
            'isArray' => true,
            'isMapping' => false,
            'provider' => EtsyTagsDataProvider::class,
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
            'identifier' => 'shipping_profile',
            'label' => 'Versandprofile',
            'isArray' => true,
            'isMapping' => true,
            'provider' => EtsyShippingProfileDataProvider::class,
            'mutators' => [
            ]
        ]);

        $template->addMapping([
            'identifier' => 'shop_sections',
            'label' => 'Shop-Abteilung',
            'isArray' => true,
            'isMapping' => true,
            'provider' => EtsyShopSectionDataProvider::class,
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

        $template->addMapping([
            'identifier' => 'price',
            'label' => 'Verkaufspreis',
            'isArray' => false,
            'isMapping' => false,
            'provider' => EtsySalesPriceDataProvider::class,
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
            'label' => 'Marktplatz',
            'defaultValue' => 0
        ]);
    }
}
