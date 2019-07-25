<?php


namespace Etsy\DataProviders;


use Etsy\EtsyServiceProvider;
use Plenty\Modules\Catalog\DataProviders\BaseDataProvider;
use Plenty\Plugin\Translation\Translator;

class EtsyTitleDataProvider extends BaseDataProvider
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
                'key' => 'titleDE',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.titleDE'),
                'required' => false
            ],
            [
                'key' => 'titleEN',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.titleEN'),
                'required' => false
            ],
            [
                'key' => 'titleFR',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.titleFR'),
                'required' => false
            ]
        ];
    }


}