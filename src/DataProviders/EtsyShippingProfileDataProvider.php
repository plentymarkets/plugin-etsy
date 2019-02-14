<?php

namespace Etsy\DataProviders;

use Plenty\Modules\Catalog\DataProviders\KeyDataProvider;

class EtsyShippingProfileDataProvider extends KeyDataProvider
{

    public function getKey(): string
    {
       return 'shipping_profiles[]';
    }

    public function getRows(): array
    {
        return [
            [
                'value' => 67847086057,
                'label' => 'DE 0,1 USA 0,2 Sonst 0,3 1-2 weeks',
                'required' => false,
            ]
        ];
    }
}