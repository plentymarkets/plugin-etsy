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
                'key' => 'who_made',
                'label' => 'who made',
                'required' => false,
            ]
        ];
    }
}