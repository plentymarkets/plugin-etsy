<?php

namespace Etsy\Contracts;

use Plenty\Modules\Item\DataLayer\Models\RecordList;

/**
 * Interface ItemDataProvider
 */
interface ItemDataProviderContract
{
	/**
	 * @param $params
	 *
	 * @return RecordList
	 */
	public function fetch(array $params = []):RecordList;
}