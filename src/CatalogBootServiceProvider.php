<?php

namespace Etsy;

use Etsy\DataProviders\EtsyCategoryDataProvider;
use Etsy\DataProviders\EtsyCurrencyDataProvider;
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
     * @var Translator
     */
    protected $translator;

    /**
     * CatalogBootServiceProvider constructor.
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param TemplateContainerContract $container
     *
     * @throws \Exception
     */
    public function boot(TemplateContainerContract $container)
    {

        /** @var Template $template */
        $template = $container->register('Etsy::catalog.test', 'Etsy::catalog.test');

        $template->addMapping([
            'identifier' => 'categories',
            'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'catalog.categories'),
            'isArray' => true,
            'isMapping' => true,
            'provider' => EtsyCategoryDataProvider::class,
            'mutators' => [
            ]
        ]);


        $template->addMapping([
            'identifier' => 'shipping_profile',
            'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'catalog.shippingProfile'),
            'isArray' => true,
            'isMapping' => true,
            'provider' => EtsyShippingProfileDataProvider::class,
            'mutators' => [
            ]
        ]);

        $template->addMapping([
            'identifier' => 'etsy_properties',
            'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'catalog.etsy'),
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
            'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'catalog.marketplace'),
            'defaultValue' => 0
        ]);
    }
}
