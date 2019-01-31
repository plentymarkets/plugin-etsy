<?php

namespace Etsy\DataProviders;


use Plenty\Modules\Catalog\DataProviders\KeyDataProvider;

class EtsyShippingProfileDataProvider extends KeyDataProvider
{

    public function getKey(): string
    {
       return '';
    }

    public function getRows(): array
    {
        return [];
    }
}