<?php

namespace Etsy\Batch;

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

	final public function run()
	{
		$result = $this->itemDataProvider->fetch();

		$this->export($result);
	}

	protected abstract function export(RecordList $recordList);
}
