<?php

namespace Etsy\Contracts;

/**
 * Interface CategoryRepositoryContract
 */
interface CategoryRepositoryContract
{
    /**
     * Get taxonomy.
     *
     * @param int $categoryId
     * @param string $lang
     * @param array $with
     *
     * @return array
     */
    public function get(int $categoryId, string $lang, array $with = []);

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
