<?php

namespace Etsy\Contracts;

use Plenty\Modules\Item\DataLayer\Models\RecordList;

/**
 * Interface ItemDataProvider
 */
interface ItemDataProviderContract
{
	/**
	 * @return RecordList
	 */
	public function fetch();
}