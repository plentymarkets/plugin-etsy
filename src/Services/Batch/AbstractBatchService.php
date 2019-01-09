<?php

namespace Etsy\Services\Batch;

use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Item\Search\Filter\MarketFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;

/**
 * Class AbstractBatchService
 */
abstract class AbstractBatchService
{
    /**
     * @var VariationElasticSearchScrollRepositoryContract
     */
    protected $variationElasticSearchScrollRepository;

    /**
     * @var SettingsHelper $settingshelper
     */
    protected $settingshelper;

    /**
     * @param VariationElasticSearchScrollRepositoryContract $variationElasticSearchScrollRepository
     */
    public function __construct(VariationElasticSearchScrollRepositoryContract $variationElasticSearchScrollRepository)
    {
        $this->settingshelper = pluginApp(SettingsHelper::class);
        $this->variationElasticSearchScrollRepository = $variationElasticSearchScrollRepository;
        $documentProcessor = pluginApp(DocumentProcessor::class);
        $elasticSearchDocument = pluginApp(DocumentSearch::class, [$documentProcessor]);

        $variationFilter = pluginApp(VariationBaseFilter::class);
        $variationFilter->isActive();

        $marketFilter = pluginApp(MarketFilter::class);
        $marketFilter->isVisibleForMarket($this->settingshelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER));

        $elasticSearchDocument->addFilter($variationFilter);
        $elasticSearchDocument->addFilter($marketFilter);
        $this->variationElasticSearchScrollRepository->addSearch($elasticSearchDocument);
    }

    /**
     * Run the batch service.
     */
    final public function run()
    {
        $result = $this->variationElasticSearchScrollRepository->execute();

        $this->export($result);
    }

    /**
     * Execute the export process.
     *
     * @param array $elasticSearchResult
     * @return mixed
     */
    protected abstract function export(array $elasticSearchResult);
}
