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
                'key' => 'material',
                'label' => 'material',
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
        ];
    }
}