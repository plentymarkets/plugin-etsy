<?php

namespace Etsy\DataProviders;

use Plenty\Modules\Catalog\DataProviders\KeyDataProvider;

/**
 * Class EtsyPropertyDataProvider
 * @package Etsy\DataProviders
 */
class EtsyPropertyDataProvider extends KeyDataProvider
{
    /**
     * @return string
     */
    public function getKey(): string
    {
        return 'etsy_properties';
    }

    /**
     * @inheritdoc
     */
    public function getRows(): array
    {
        return [
            [
                'id' => 1,
                'label' => 'sdasdas',
                'required' => false,
            ],
            [
                'id' => 2,
                'label' => 'dasda',
                'required' => false,
            ]
        ];
    }
}