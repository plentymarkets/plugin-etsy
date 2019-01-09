<?php

namespace Etsy\Services\Batch;

use Plenty\Modules\Item\DataLayer\Models\RecordList;

use Etsy\Contracts\ItemDataProviderContract;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;

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
     * @param VariationElasticSearchScrollRepositoryContract $variationElasticSearchScrollRepository
     */
    public function __construct(VariationElasticSearchScrollRepositoryContract $variationElasticSearchScrollRepository)
    {
        $this->variationElasticSearchScrollRepository = $variationElasticSearchScrollRepository;
    }

    /**
     * Run the batch service.
     *
     * @param array $params The params needed by the data provider. Eg. the last run.
     */
    final public function run(array $params = [])
    {
        $test = 0;

        /*
        $result = $this->itemDataProvider->fetch($params);

        $this->export($result);
         */
    }

    /**
     * Execute the export process.
     *
     * @param array $elasticSearchResult
     * @return mixed
     */
    protected abstract function export(array $elasticSearchResult);
}
