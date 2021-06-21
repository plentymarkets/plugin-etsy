<?php


namespace Etsy\Catalog;


use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Catalog\Contracts\CatalogRuntimeConfigContract;
use Plenty\Modules\Catalog\Contracts\TemplateContract;
use Plenty\Modules\Catalog\Models\Catalog;

class EtsyCatalogRuntimeConfig implements CatalogRuntimeConfigContract
{

    /**
     * Will be run before every export and is meant to provide filters on the template
     * which can't be defined before the runtime of the export
     *
     * @param TemplateContract $template
     * @param Catalog $catalog
     */
    public function applyRuntimeConfig(TemplateContract $template, Catalog $catalog)
    {
        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = pluginApp(SettingsHelper::class);
        $template->addFilter([
            [
                'name' => 'variationMarket.isVisibleForMarket',
                'params' => [
                    [
                        "name" => "marketId",
                        "value" => $settingsHelper->get($settingsHelper::SETTINGS_ORDER_REFERRER)
                    ]
                ]
            ]
        ]);
    }

    /**
     * Will be run before every preview and is meant to provide filters on the template
     * which can't be defined before the runtime of the preview
     *
     * @param TemplateContract $template
     * @param Catalog $catalog
     */
    public function applyPreviewRuntimeConfig(TemplateContract $template, Catalog $catalog)
    {
        // use ui filters
    }
}