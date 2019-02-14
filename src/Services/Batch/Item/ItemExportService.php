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
    private $app;

    /**
     * @var StartListingService
     */
    private $startService;

    /**
     * @var UpdateListingService
     */
    private $updateService;

    /**
     * @var Translator
     */
    private $translator;

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
                $test = true;
            }
        }
        /*

        $this->getLogger(__FUNCTION__)
            ->addReference('etsyExportListCount', count($records))
            ->debug('Etsy::item.exportRecord');

        foreach($records as $record)
        {
            try
            {
                if($this->isListingCreated($record))
                {
                    $this->updateService->update($record);
                }
                else
                {
                    $this->startService->start($record);
                }
            }
            catch(\Exception $ex)
            {
                if (strpos($ex->getMessage(), 'Invalid data param type "shipping_template_id"') !== false)
                {
                    $this->getLogger(__FUNCTION__)
                        ->addReference('variationId', $record->variationBase->id)
                        ->error('Etsy::item.startListingErrorShippingProfile', [
                            'exception' => $ex->getMessage(),
                            'instruction' => $this->translator->trans('Etsy::instructions.instructionShippingProfile')
                        ]);
                }
                elseif (strpos($ex->getMessage(), 'Invalid data param type "taxonomy_id"') !== false)
                {
                    $this->getLogger(__FUNCTION__)
                        ->addReference('variationId', $record->variationBase->id)
                        ->error('Etsy::item.startListingErrorTaxonomyId', [
                            'exception' => $ex->getMessage(),
                            'instruction' => $this->translator->trans('Etsy::instructions.instructionShippingProfile')
                        ]);
                }
                elseif (strpos($ex->getMessage(), 'Oh dear, you cannot sell this item on Etsy') !== false)
                {
                    $this->getLogger(__FUNCTION__)
                        ->addReference('variationId', $record->variationBase->id)
                        ->error('Etsy::item.startListingErrorInvalidItem', [
                            'exception' => $ex->getMessage(),
                            'instruction' => $this->translator->trans('Etsy::instructions.instructionInvalidItem')
                        ]);
                }
                else
                {
                    $this->getLogger(__FUNCTION__)
                        ->addReference('variationId', $record->variationBase->id)
                        ->error('Etsy::item.startListingError', $ex->getMessage());
                }
            }
        }

         */
    }

    /**
     * Check if listing is created.
     * @param array $listing
     * @return bool
     */
    private function isListingCreated(array $listing):bool
    {
//todo: anpassen
        if (isset($listing['main']['data']['skus'][0]['sku'])
            && $listing['main']['data']['skus'][0]['marketId'] === $this->settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER))
        {
            return true;
        }
        else {
            return false;
        }

        /*
        $listingId = (string) $record->variationMarketStatus->sku;

        if(strlen($listingId))
        {
            return true;
        }

        return false;
         */
    }
}
