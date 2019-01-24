<?php

namespace Etsy\DataProviders;


use Plenty\Modules\Catalog\DataProviders\KeyDataProvider;

class EtsyCategoryDataProvider extends KeyDataProvider
{
    /**
     * @return string
     */
    public function getKey(): string
    {
        return 'categories';
    }

    /**
     * @inheritdoc
     */
    public function getRows(): array
    {
        return [
            [
                'id' => 1069,
                'label' => 'Teetassen & Teesets',
                'required' => false,
            ],
            [
                'id' => 1102,
                'label' => 'Büro- & Schulbedarf',
                'required' => false,
            ],
            [
                'id' => 1069,
                'label' => 'Office & School Supplies',
                'required' => false,
            ],
            [
                'id' => 1069,
                'label' => 'Fournitures école et bureau',
                'required' => false,
            ]
        ];
    }

    /**
     'id' => '1069',
    'name' => 'Teetassen & Teesets',
    'id' => '1102',
    'name' => 'Büro- & Schulbedarf',
    englisch:
    'id' => '1069',
    'name' => 'Tea Cups & Sets',
    'id' => '1102',
    'name' => 'Office & School Supplies',
    französisch:
    'id' => '1069',
    'name' => 'Tasses et services à thé',
    'id' => '1102',
    'name' => 'Fournitures école et bureau',
     */
}

