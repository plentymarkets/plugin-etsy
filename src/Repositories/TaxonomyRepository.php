<?php

namespace Etsy\Repositories;

use Etsy\Contracts\TaxonomyRepositoryContract;
use Etsy\Models\Taxonomy;
use PayPal\Api\Tax;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;

/**
 * Class TaxonomyRepository
 */
class TaxonomyRepository implements TaxonomyRepositoryContract
{
    /**
     * @var DataBase $database
     */
    protected $database;

    /**
     * @param DataBase $database
     */
    public function __construct(DataBase $database)
    {
        $this->database = $database;
    }

    /**
     * @inheritdoc
     */
    public function get(int $taxonomyId, array $with = []): Taxonomy
    {
        /** @var Taxonomy $taxonomy */
        $taxonomy = $this->database->find(Taxonomy::class, $taxonomyId);

        return $taxonomy;
    }

    /**
     * @inheritdoc
     */
    public function all(array $filters = [], array $with = [])
    {
        $query = $this->database->query(Taxonomy::class);

        foreach ($filters as $field => $filter) {
            $query->where($field, '=', $filter);
        }

        return $query->get();
    }

    /**
     * Save given taxonomy.
     *
     * @param Taxonomy $taxonomy
     */
    public function save(Taxonomy $taxonomy)
    {
        $this->database->save($taxonomy);
    }
}
