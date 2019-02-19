<?php

namespace Etsy\DataProviders;

use Plenty\Modules\Catalog\DataProviders\BaseDataProvider;

class EtsySalesPriceDataProvider  extends BaseDataProvider
{
    /**
     * @inheritdoc
     */
    public function getRows(): array
    {
        return [
            [
                'key' => 'who_made',
                'label' => 'who made',
                'required' => true,
            ],
            [
                'key' => 'when_made',
                'label' => 'when made',
                'required' => true,
            ],
            [
                'key' => 'is_supply',
                'label' => 'is supply',
                'required' => true,
            ],
            [
                'key' => 'materials',
                'label' => 'materials',
                'required' => false,
            ],
            [
                'key' => 'occasion',
                'label' => 'occasion',
                'required' => false,
            ],
            [
                'key' => 'recipient',
                'label' => 'recipient',
                'required' => false,
            ],
            [
                'key' => 'shop_section_id',
                'label' => 'shop section',
                'required' => false,
            ],
            [
                'key' => 'is_customizable',
                'label' => 'is customizable',
                'required' => false,
            ],
            [
                'key' => 'recipient',
                'label' => 'recipient',
                'required' => false,
            ],
            [
                'key' => 'non_taxable',
                'label' => 'non taxable',
                'required' => false,
            ],
            [
                'key' => 'recipient',
                'label' => 'recipient',
                'required' => false,
            ],
            [
                'key' => 'processing_min',
                'label' => 'processing min',
                'required' => false,
            ],
            [
                'key' => 'processing_max',
                'label' => 'processing max',
                'required' => false,
            ],
            [
                'key' => 'style',
                'label' => 'style',
                'required' => false,
            ],
            [
                'key' => 'item_weight',
                'label' => 'item weight',
                'required' => false,
            ],
            [
                'key' => 'item_height',
                'label' => 'item height',
                'required' => false,
            ],
            [
                'key' => 'item_length',
                'label' => 'item length',
                'required' => false,
            ],
            [
                'key' => 'item_width',
                'label' => 'item_width',
                'required' => false,
            ]
        ];
    }
}
