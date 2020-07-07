<?php

namespace Etsy\Services\Batch\Item;

use Etsy\EtsyServiceProvider;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Exceptions\ValidationException;

use Etsy\Services\Item\UpdateListingService;
use Etsy\Services\Batch\AbstractBatchService;
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
        $memoryUsage = [
            'memoryUsage' => memory_get_usage(true),
            'memoryPeakUsage' =>  memory_get_peak_usage(true)
        ];

        $this->getLogger(EtsyServiceProvider::ITEM_EXPORT_SERVICE)
            ->debug(EtsyServiceProvider::PLUGIN_NAME . '::log.memoryUsage', $memoryUsage);

        // Todo this is to analyse a cron problem that may be caused by a memory overflow
        // Todo here we check if the used memory is at or over 1.5 gb since this is close to the available
        // Todo 2 gb and therefor we should log this
        if ($memoryUsage['memoryUsage'] >= 1610612736 || $memoryUsage['memoryPeakUsage'] >= 1610612736) {
            $this->getLogger(EtsyServiceProvider::ITEM_EXPORT_SERVICE)
                ->warning(EtsyServiceProvider::PLUGIN_NAME . '::log.highMemoryUsage', $memoryUsage);
        }

        $listings = [];

        foreach ($catalogResult as $variation) {

            /**
             * skipping every variation with the do not export property
             */
            if (isset($variation['do_not_export'])){
                continue;
            }

            //for convenience we get rid of all skus that are not related to Etsy
            $skus = [];

            foreach ($variation['skus'] as $sku) {
                if ($sku['marketId'] == $this->settingshelper->get($this->settingshelper::SETTINGS_ORDER_REFERRER)) {
                    $skus[] = $sku;
                    break;
                }
            }

            $variation['skus'] = $skus;

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
                $this->getLogger(EtsyServiceProvider::ITEM_EXPORT_SERVICE)
                    ->addReference('itemId', $listing['main']['itemId'])
                    ->warning(EtsyServiceProvider::PLUGIN_NAME . 'item.itemExportError', [
                        $exception->getMessage()
                    ]);
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
        $isListed = false;

        foreach ($listing as $variation) {
            if (isset($variation['skus'][0]['parentSku'])) {
                $isListed = true;
            }
        }

        return $isListed;
    }
}
