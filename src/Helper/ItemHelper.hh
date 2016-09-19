<?hh //strict

namespace Etsy\Helper;

use Plenty\Plugin\Application;
use Plenty\Modules\Helper\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Item\DataLayer\Models\Record;

class ItemHelper
{
    /**
    * Application $app
    */
    private Application $app;

    /**
    * UrlBuilderRepositoryContract $urlBuilderRepository
    */
    private UrlBuilderRepositoryContract $urlBuilderRepository;

    public function __construct(
        Application $app,
        UrlBuilderRepositoryContract $urlBuilderRepository
    )
    {
        $this->app = $app;
        $this->urlBuilderRepository = $urlBuilderRepository;
    }

    /**
     * Get the stock based on the settings.
     *
     * @param Record $item
     * @return int
     */
    public function getStock(Record $item):int
    {
        if($item->variationBase->limitOrderByStockSelect == 2)
        {
            $stock = 999;
        }
        elseif($item->variationBase->limitOrderByStockSelect == 1 && $item->variationStock->stockNet > 0)
        {
            if($item->variationStock->stockNet > 999)
            {
                $stock = 999;
            }
            else
            {
                $stock = $item->variationStock->stockNet;
            }
        }
        elseif($item->variationBase->limitOrderByStockSelect == 0)
        {
            if($item->variationStock->stockNet > 999)
            {
                $stock = 999;
            }
            else
            {
                if($item->variationStock->stockNet > 0)
                {
                    $stock = $item->variationStock->stockNet;
                }
                else
                {
                    $stock = 0;
                }
            }
        }
        else
        {
            $stock = 0;
        }

        return $stock;
    }

    public function getItemProperty(Record $record, string $propertyName):mixed
    {
        switch($propertyName)
        {
            case 'shipping_template_id':
                return 28734983909;

            case 'who_made':
                return 'i_did';

            case 'is_supply':
                return false;

            case 'when_made':
                return '1990_1996';

            default:
                return '';
        }
    }

    /**
     * Get list of images for current item.
     *
     * @param array<mixed,mixed> $list
     * @param string $imageSize
     * @return array<int, string>
     */
    public function getImageList(array<mixed,mixed> $list, string $imageSize = 'normal'):array<string>
    {
        $imageList = [];

        foreach($list as $image)
        {
            if(is_array($image) && array_key_exists('path', $image))
            {
                $imageList[] = $this->urlBuilderRepository->getImageUrl((string) $image['path'], null, $imageSize, $image['fileType'], $image['type'] == 'external');
            }
        }

        return $imageList;
    }

    public function getEtsyVariationProperties():array<int,string>
    {
        $map = [
            200 => 'Color',
            513 => 'Custom 1',
            514 => 'Custom 2',
            515 => 'Device',
            504 => 'Diameter',
            501 => 'Dimensions',
            502 => 'Fabric',
            500 => 'Finish',
            503 => 'Flavor',
            505 => 'Height',
            506 => 'Length',
            507 => 'Material',
            508 => 'Pattern',
            509 => 'Scent',
            510 => 'Style',
            100 => 'Size',
            511 => 'Weight',
            512 => 'Width',
        ];

        return $map;
    }

    public function getEtsyQualifierProperties():array<int,string>
    {
        $map = [
            302 => 'Diameter Scale',
            303 => 'Dimensions Scale',
            304 => 'Height Scale',
            305 => 'Length Scale',
            266817057 => 'Recipient',
            300 => 'Sizing Scale',
            301 => 'Weight Scale',
            306 => 'Width Scale',
        ];

        return $map;
    }

    public function getEtsyMarketplaceAttributes():array<string,mixed>
    {
        $map = [
            'who_made' => [
                'i_did',
                'collective',
                'someone_else'
            ],

            'when_made' => [
                'made_to_order',
                '2010_2016',
                '2000_2009',
                '1997_1999',
                'before_1997',
                '1990_1996',
                '1980s',
                '1970s',
                '1960s',
                '1950s',
                '1940s',
                '1930s',
                '1920s',
                '1910s',
                '1900s',
                '1800s',
                '1700s',
                'before_1700'
            ],
            'item_weight_units' => [
                'oz',
                'lb',
                'g',
                'kg',
            ],
            'item_dimensions_unit' => [
                'in',
                'ft',
                'mm',
                'cm',
                'm',
            ],
            'recipient' => [
                'men',
                'women',
                'unisex_adults',
                'teen_boys',
                'teen_girls',
                'teens',
                'boys',
                'girls',
                'children',
                'baby_boys',
                'baby_girls',
                'babies',
                'birds',
                'cats',
                'dogs',
                'pets',
                'not_specified'
            ],
            'occasion' => [
                'anniversary',
                'baptism',
                'bar_or_bat_mitzvah',
                'birthday',
                'canada_day',
                'chinese_new_year',
                'cinco_de_mayo',
                'confirmation',
                'christmas',
                'day_of_the_dead',
                'easter',
                'eid',
                'engagement',
                'fathers_day',
                'get_well',
                'graduation',
                'halloween',
                'hanukkah',
                'housewarming',
                'kwanzaa',
                'prom',
                'july_4th',
                'mothers_day',
                'new_baby',
                'new_years',
                'quinceanera',
                'retirement',
                'st_patricks_day',
                'sweet_16',
                'sympathy',
                'thanksgiving',
                'valentines',
                'wedding'
            ],
        ];

        return $map;
    }
}
