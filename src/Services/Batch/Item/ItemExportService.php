<?php

namespace Etsy\Services\Batch\Item;

use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Item\DataLayer\Models\RecordList;

use Etsy\Services\Item\UpdateListingService;
use Etsy\Services\Batch\AbstractBatchService;
use Etsy\Factories\ItemDataProviderFactory;
use Etsy\Services\Item\StartListingService;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;

/**
 * Class ItemExportService
 */
class ItemExportService extends AbstractBatchService
{
    use Loggable;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var StartListingService
     */
    protected $startService;

    /**
     * @var UpdateListingService
     */
    protected $updateService;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * ItemExportService constructor.
     * @param Application $app
     * @param StartListingService $startService
     * @param UpdateListingService $updateService
     * @param Translator $translator
     * @param SettingsHelper $settingsHelper
     */
    public function __construct(
        Application $app,
        StartListingService $startService,
        UpdateListingService $updateService,
        Translator $translator,
        SettingsHelper $settingsHelper
    )
    {
        $this->app = $app;
        $this->startService = $startService;
        $this->updateService = $updateService;
        $this->translator = $translator;
        $this->settingshelper = $settingsHelper;

        parent::__construct(pluginApp(VariationElasticSearchScrollRepositoryContract::class));
    }

    /**
     * Export all items.
     * @param array $catalogResult
     * @return void
     */
    protected function export(array $catalogResult)
    {
        $listings = [];

        foreach ($catalogResult as $variation) {

            if ($variation['isMain'] == true) {
                $listings[$variation['itemId']]['main'] = $variation;
                continue;
            }
            $listings[$variation['itemId']][] = $variation;
        }

        foreach ($listings as $listing) {
            try
            {
                if($this->isListingCreated($listing))
                {
                    $this->updateService->update($listing);
                }
                else
                {
                    $this->startService->start($listing);
                }
            } catch (\Exception $exception) {
                //todo übersetzen
                $this->getLogger(__FUNCTION__)
                    ->addReference('itemId', $listing['main']['itemId'])
                    ->warning('Listing export error', [
                        $exception->getMessage()
                    ]);
            }
            }
        }
    }

    /**
     * Check if listing is created.
     * @param array $listing
     * @return bool
     */
    protected function isListingCreated(array $listing):bool
    {
        if (isset($listing['main']['skus'][0]['sku']))
        {
            return true;
        }
        else {
            return false;
        }
    }
}
