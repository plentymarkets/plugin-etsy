<?php

namespace Etsy\Contracts;

/**
 * Interface TaxonomyDataProviderContract
 */
interface TaxonomyDataProviderContract
{
	/**
	 * @return array
	 */
    public function fetch();
}
