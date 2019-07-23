<?php


namespace Etsy\DataProviders;


use Etsy\EtsyServiceProvider;
use Plenty\Modules\Catalog\DataProviders\BaseDataProvider;
use Plenty\Plugin\Translation\Translator;

class EtsyDescriptionDataProvider extends BaseDataProvider
{

    /** @var Translator  */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getRows(): array
    {
        return [
            [
                'key' => 'descriptionDE',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.descriptionDE'),
                'required' => false
            ],
            [
                'key' => 'descriptionEN',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.descriptionEN'),
                'required' => false
            ],
            [
                'key' => 'descriptionFR',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.descriptionFR'),
                'required' => false
            ]
        ];
    }


}