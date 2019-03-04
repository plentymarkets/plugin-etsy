<?php

namespace Etsy\DataProviders;

use Etsy\EtsyServiceProvider;
use Plenty\Modules\Catalog\DataProviders\BaseDataProvider;
use Plenty\Plugin\Translation\Translator;

class EtsySalesPriceDataProvider  extends BaseDataProvider
{
    /**
     * @var Translator
     */
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
                'key' => 'sales_price',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.sales_price'),
                'required' => true,
            ],
            [
                'key' => 'currency',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.currency'),
                'required' => true,
            ]
        ];
    }
}
