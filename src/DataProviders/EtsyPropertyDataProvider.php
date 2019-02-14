<?php

namespace Etsy\DataProviders;

use Plenty\Modules\Catalog\DataProviders\BaseDataProvider;

/**
 * Class EtsyPropertyDataProvider
 * @package Etsy\DataProviders
 */
class EtsyPropertyDataProvider extends BaseDataProvider
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
            ]
        ];
    }
}