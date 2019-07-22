<?php

namespace Etsy\DataProviders;

use Etsy\EtsyServiceProvider;
use Plenty\Modules\Catalog\DataProviders\BaseDataProvider;
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
                'key' => 'renew',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.renew'),
                'required' => false,
            ],
            [
                'key' => 'title',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.title'),
                'required' => false,
            ],
            [
                'key' => 'description',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.description'),
                'required' => false,
            ],
            [
                'key' => 'who_made',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.who_made'),
                'required' => true,
            ],
            [
                'key' => 'when_made',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.when_made'),
                'required' => true,
            ],
            [
                'key' => 'is_supply',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.is_supply'),
                'required' => true,
            ],
            [
                'key' => 'materials',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.material'),
                'required' => false,
            ],
            [
                'key' => 'occasion',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.occasion'),
                'required' => false,
            ],
            [
                'key' => 'recipient',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.recipient'),
                'required' => false,
            ],
            [
                'key' => 'is_customizable',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.is_customizable'),
                'required' => false,
            ],
            [
                'key' => 'non_taxable',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.non_taxable'),
                'required' => false,
            ],
            [
                'key' => 'processing_min',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.processing_min'),
                'required' => false,
            ],
            [
                'key' => 'processing_max',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.processing_max'),
                'required' => false,
            ],
            [
                'key' => 'style',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.style'),
                'required' => false,
            ],
            [
                'key' => 'tags',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.tags'),
                'required' => false,
            ],
            [
                'key' => 'item_weight',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.item_weight'),
                'required' => false,
            ],
            [
                'key' => 'item_height',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.item_height'),
                'required' => false,
            ],
            [
                'key' => 'item_length',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.item_length'),
                'required' => false,
            ],
            [
                'key' => 'item_width',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.item_width'),
                'required' => false,
            ],
            [
                'key' => 'do_not_export',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.do_not_export'),
                'required' => false,
            ]
        ];
    }
}