<?php

namespace Etsy\Contracts;

use Etsy\Models\Taxonomy;

/**
 * Interface TaxonomyRepositoryContract
 */
interface TaxonomyRepositoryContract
{
	/**
     * Get taxonomy.
     *
	 * @param int $taxonomyId
	 * @param string $lang
     * @param array $with
     *
	 * @return Taxonomy
	 */
	public function get(int $taxonomyId, string $lang, array $with = []): Taxonomy;

	/**
     * Get all taxonomies.
     *
	 * @param array $filters
     * @param array $with
     *
	 * @return array
	 */
	public function all(array $filters = [], array $with = []);
}
