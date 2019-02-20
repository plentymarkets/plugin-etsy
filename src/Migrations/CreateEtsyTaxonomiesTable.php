<?php

namespace Etsy\Migrations;

use Etsy\Api\Services\TaxonomyService;
use Etsy\Contracts\TaxonomyRepositoryContract;
use Etsy\Models\Taxonomy;
use PayPal\Api\Tax;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

/**
 * Class CreateEtsyTaxonomysTable
 *
 * @package Etsy\Migrations
 */
class CreateEtsyTaxonomiesTable
{
    public function run(Migrate $migrate, Taxonomy $taxonomy, TaxonomyRepositoryContract $taxonomyRepository)
    {
        $migrate->createTable('Etsy\Models\Taxonomy');

        /** @var TaxonomyService $taxonomyService */
        $taxonomyService = pluginApp(TaxonomyService::class);
        $taxonomies = $taxonomyService->getSellerTaxonomy('en');

        $savableArrays = [];

        foreach ($taxonomies as $data) {
            $savableArrays = $this->createSavableArray($data);
        }

        $taxonomies = $taxonomyService->getSellerTaxonomy('de');

        foreach ($taxonomies as $taxonomy) {
            $savableArrays[$taxonomy['id']]['nameDe'] = $taxonomy['name'];
        }

        $taxonomies = $taxonomyService->getSellerTaxonomy('fr');

        foreach ($taxonomies as $taxonomy) {
            $savableArrays[$taxonomy['id']]['nameFr'] = $taxonomy['name'];
        }

        foreach ($savableArrays as $savableArray) {
            /** @var Taxonomy $taxonomy */
            $taxonomy = pluginApp(Taxonomy::class);
            $taxonomy->fillByAttributes($savableArray);
            $taxonomyRepository->save($taxonomy);
        }
    }

    protected function createSavableArray($taxonomy)
    {
        $result = [];

        $result['id'] = [
            'id' => $taxonomy['id'],
            'children' => $taxonomy['children'],
            'level' => $taxonomy['level'],
            'nameEn' => $taxonomy['name'],
            'parentId' => $taxonomy['parentId'],
            'isLeaf' => !(isset($taxonomy['children']) && count($taxonomy['children'])),
            'path' => $taxonomy['path'],
        ];

        if (count($taxonomy['children'])) {
            foreach ($taxonomy['children'] as $child) {
                $result[] = $this->createSavableArray($child);
            }
        }

        return $result;
    }
}