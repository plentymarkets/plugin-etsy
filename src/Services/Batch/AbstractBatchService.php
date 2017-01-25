<?php

namespace Etsy\Services\Batch;

use Plenty\Modules\Item\DataLayer\Models\RecordList;

use Etsy\Contracts\ItemDataProviderContract;

/**
 * Class AbstractBatchService
 */
abstract class AbstractBatchService
{
	/**
	 * @var ItemDataProviderContract
	 */
	private $itemDataProvider;

	/**
	 * @param ItemDataProviderContract $itemDataProvider
	 */
	public function __construct(ItemDataProviderContract $itemDataProvider)
	{
		$this->itemDataProvider = $itemDataProvider;
	}

	/**
	 * Run the batch service.
	 *
	 * @param array $params The params needed by the data provider. Eg. the last run.
	 */
	final public function run(array $params = [])
	{
		$result = $this->itemDataProvider->fetch($params);

		$this->export($result);
	}

	/**
	 * Execute the export process.
	 *
	 * @param RecordList $recordList
	 *
	 * @return void
	 */
	protected abstract function export(RecordList $recordList);
}
