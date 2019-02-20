<?php

namespace Etsy\Repositories;

use Etsy\Contracts\TaxonomyRepositoryContract;
use Etsy\Models\Taxonomy;
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
       //todo
        $taxonomy = pluginApp(Taxonomy::class);

        return $taxonomy;
    }

    /**
     * @inheritdoc
     */
    public function all(array $filters = [], array $with = [])
    {
        return $this->database->query(Taxonomy::class)->where('level', '=', 0);
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
