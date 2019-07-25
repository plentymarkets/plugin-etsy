<?php


namespace Etsy\DataProviders;


use Etsy\EtsyServiceProvider;
use Plenty\Modules\Catalog\DataProviders\BaseDataProvider;
use Plenty\Plugin\Translation\Translator;

class EtsyTagsDataProvider extends BaseDataProvider
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
                'key' => 'tagsDE',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.tagsDE'),
                'required' => false
            ],
            [
                'key' => 'tagsEN',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.tagsEN'),
                'required' => false
            ],
            [
                'key' => 'tagsFR',
                'label' => $this->translator->trans(EtsyServiceProvider::PLUGIN_NAME.'::catalog.tagsFR'),
                'required' => false
            ]
        ];
    }


}