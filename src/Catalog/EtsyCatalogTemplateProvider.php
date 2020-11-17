<?php

namespace Etsy\Catalog;

use Etsy\DataProviders\EtsyCategoryDataProvider;
use Etsy\DataProviders\EtsyDescriptionDataProvider;
use Etsy\DataProviders\EtsyPropertyDataProvider;
use Etsy\DataProviders\EtsySalesPriceDataProvider;
use Etsy\DataProviders\EtsyShippingProfileDataProvider;
use Etsy\DataProviders\EtsyShopSectionDataProvider;
use Etsy\DataProviders\EtsyTagsDataProvider;
use Etsy\DataProviders\EtsyTitleDataProvider;
use Plenty\Modules\Catalog\Contracts\CatalogRuntimeConfigProviderContract;
use Plenty\Modules\Catalog\Contracts\CatalogTemplateProviderContract;

/**
 * Class EtsyCatalogTemplateProvider
 */
class EtsyCatalogTemplateProvider implements CatalogTemplateProviderContract, CatalogRuntimeConfigProviderContract
{

    /**
     * Returns the different mapping sections including the information which data provider fills them. Each entry in the array represents a section of the catalogue UI.
     *
     * @return array
     */
    public function getMappings(): array
    {
        return [
            [
                'identifier' => 'title',
                'label' => 'Titel',
                'isArray' => true,
                'isMapping' => false,
                'provider' => EtsyTitleDataProvider::class,
                'mutators' => [
                ]
            ],
            [
                'identifier' => 'description',
                'label' => 'Beschreibung',
                'isArray' => true,
                'isMapping' => false,
                'provider' => EtsyDescriptionDataProvider::class,
                'mutators' => [
                ]
            ],
            [
                'identifier' => 'tags',
                'label' => 'Tags',
                'isArray' => true,
                'isMapping' => false,
                'provider' => EtsyTagsDataProvider::class,
                'mutators' => [
                ]
            ],
            [
                'identifier' => 'categories',
                'label' => 'Kategorien',
                'isArray' => true,
                'isMapping' => true,
                'provider' => EtsyCategoryDataProvider::class,
                'mutators' => [
                ]
            ],
            [
                'identifier' => 'shipping_profile',
                'label' => 'Versandprofile',
                'isArray' => true,
                'isMapping' => true,
                'provider' => EtsyShippingProfileDataProvider::class,
                'mutators' => [
                ]
            ],
            [
                'identifier' => 'shop_sections',
                'label' => 'Shop-Abteilung',
                'isArray' => true,
                'isMapping' => true,
                'provider' => EtsyShopSectionDataProvider::class,
                'mutators' => [
                ]
            ],
            [
                'identifier' => 'etsy_properties',
                'label' => 'Eigenschaften',
                'isArray' => false,
                'isMapping' => false,
                'provider' => EtsyPropertyDataProvider::class,
                'mutators' => [
                ]
            ],
            [
                'identifier' => 'price',
                'label' => 'Verkaufspreis',
                'isArray' => false,
                'isMapping' => false,
                'provider' => EtsySalesPriceDataProvider::class,
                'mutators' => [
                ]
            ]
        ];
    }

    /**
     * Returns the filters that will be applied in each export of templates that will be booted by this provider.
     *
     * @return array
     */
    public function getFilter(): array
    {
        return [];
    }

    /**
     * Returns the callback functions that will be applied to the raw data (so before the mapping occurs) of each item in the export. Every callback function will receive an array of the raw item data and should return this array with the changes that should be applied (e.g. function (array $item){ --your code-- return $item}).
     *
     * @return callable[]
     */
    public function getPreMutators(): array
    {
        return [];
    }

    /**
     * Returns the callback functions that will be applied to the mapped data of each item in the export. Every callback function will receive an array of the mapped item data und should return this array with the changes that should be applied (e.g. function (array $item){ --your code-- return $item}).
     *
     * @return callable[]
     */
    public function getPostMutators(): array
    {
        return [];
    }

    /**
     * Returns a callback function that is called if a field with the specific key "sku" got mapped. The function will receive the value that got mapped, the raw data array of this item and the type of the mapped source. It should return the new value (e.g. function ($value, array $item, $mappingType){ --your code-- return $value})).
     *
     * @return callable
     */
    public function getSkuCallback(): callable
    {
        return function ($variation) {
            return $variation;
        };
    }

    /**
     * Returns an array of settings that will be displayed in the UI of each catalogue with a template that uses this provider. The selected values for all those settings can then be used in the export.
     *
     * @return array
     */
    public function getSettings(): array
    {
        return [];
    }

    /**
     * Returns an array of meta information which can be used to forward information to the export which could otherwise not be received.
     *
     * @return array
     */
    public function getMetaInfo(): array
    {
        return [];
    }

    /**
     * Determines if a preview can be exported through catalogs that use the specific template
     *
     * @return bool
     */
    public function isPreviewable(): bool
    {
        return true;
    }

    /**
     * Returns a class name through which the export can be configured with information that isn't known before
     * the export runtime
     *
     * Should return null if this feature doesn't get used
     *
     * @return string|null
     */
    public function getRuntimeConfigClass(): ?string
    {
        //do vdi stuff
    }

    /**
     * Returns a class name through which the export result can be converted into the necessary format (e.g. json or csv)
     *
     * Should return null if this feature doesn't get used
     *
     * @return string|null
     */
    public function getResultConverterClass(): ?string
    {
        return null;
    }
}