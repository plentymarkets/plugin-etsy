<?php

namespace Etsy\Contracts;

/**
 * Interface EtsyTaxonomyRepositoryContract
 */
interface EtsyTaxonomyRepositoryContract
{
	/**
	 * @param int $taxonomyId
	 * @param string $language
	 * @return array
	 */
	public function findById($taxonomyId, $language);

	/**
	 * @param int $taxonomyId
	 * @param string $language
	 * @return array
	 */
	public function all($taxonomyId, $language);
}
