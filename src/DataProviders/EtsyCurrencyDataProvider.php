<?php

namespace Etsy\DataProviders;

use Plenty\Modules\Catalog\DataProviders\KeyDataProvider;

/**
 * Class EtsyCurrencyDataProvider
 * @package Etsy\DataProviders
 */
class EtsyCurrencyDataProvider extends KeyDataProvider
{
    /**
     * @return string
     */
    public function getKey(): string
    {
        return 'categories[]';
    }

    /**
     * @inheritdoc
     */
    public function getRows(): array
    {
        return [
            [
                'value' => 1069,
                'label' => 'Teetassen & Teesets',
                'required' => false,
            ],
            [
                'value' => 1102,
                'label' => 'Büro- & Schulbedarf',
                'required' => false,
            ],
        ];
    }
}