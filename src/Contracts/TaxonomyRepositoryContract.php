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
     * @param array $with
     *
	 * @return Taxonomy
	 */
	public function get(int $taxonomyId, array $with = []): Taxonomy;

	/**
     * Get all taxonomies.
     *
	 * @param array $filters
     * @param array $with
     *
	 * @return array
	 */
	public function all(array $filters = [], array $with = []);


    /**
     * Save given taxonomy.
     *
     * @param Taxonomy $taxonomy
     */
    public function save(Taxonomy $taxonomy);
}
