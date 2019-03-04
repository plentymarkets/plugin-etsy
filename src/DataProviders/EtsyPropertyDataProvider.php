<?php

namespace Etsy\DataProviders;

use Etsy\EtsyServiceProvider;
use Plenty\Modules\catalog\DataProviders\BaseDataProvider;
use Plenty\Plugin\Translation\Translator;

/**
 * Class EtsyPropertyDataProvider
 * @package Etsy\DataProviders
 */
class EtsyPropertyDataProvider extends BaseDataProvider
{
    protected $translator;

    /**
     * EtsySalesPriceDataProvider constructor.
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritdoc
     */
    public function getRows(): array
    {
        return [
            [
                'key' => 'who_made',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.who_made'),
                'required' => true,
            ],
            [
                'key' => 'when_made',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.when_made'),
                'required' => true,
            ],
            [
                'key' => 'is_supply',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.is_supply'),
                'required' => true,
            ],
            [
                'key' => 'materials',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.material'),
                'required' => false,
            ],
            [
                'key' => 'occasion',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.occasion'),
                'required' => false,
            ],
            [
                'key' => 'recipient',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.recipient'),
                'required' => false,
            ],
            [
                'key' => 'shop_section_id',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.shop'),
                'required' => false,
            ],
            [
                'key' => 'is_customizable',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.is_customizable'),
                'required' => false,
            ],
            [
                'key' => 'non_taxable',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.non_taxable'),
                'required' => false,
            ],
            [
                'key' => 'processing_min',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.processing_min'),
                'required' => false,
            ],
            [
                'key' => 'processing_max',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.processing_max'),
                'required' => false,
            ],
            [
                'key' => 'style',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.style'),
                'required' => false,
            ],
            [
                'key' => 'item_weight',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.item_weight'),
                'required' => false,
            ],
            [
                'key' => 'item_height',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.item_height'),
                'required' => false,
            ],
            [
                'key' => 'item_length',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.item_length'),
                'required' => false,
            ],
            [
                'key' => 'item_width',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog_property.item_width'),
                'required' => false,
            ]
        ];
    }
}