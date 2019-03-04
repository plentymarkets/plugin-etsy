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
                'key' => 'sales_price',
                'label' => 'sales price',
                'required' => true,
            ],
            [
                'key' => 'currency',
                'label' => 'currency',
                'required' => true,
            ]
        ];
    }
}
