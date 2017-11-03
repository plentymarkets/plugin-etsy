<?php

namespace Etsy\Contracts;

/**
 * Interface TaxonomyRepositoryContract
 */
interface PropertyRepositoryContract
{
    /**
     * Get all properties.
     *
     * @param array $filters
     * @param array $with
     *
     * @return array
     */
    public function all(array $filters = [], array $with = []);

    /**
     * Get all system properties.
     *
     * @param array $filters
     * @param array $with
     *
     * @return array
     */
    public function systemProperties(array $filters = [], array $with = []);
}
