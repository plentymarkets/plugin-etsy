<?php
namespace Etsy\Migrations;

use Etsy\Api\Services\TaxonomyService;
use Etsy\Models\Taxonomy;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

/**
 * Class CreateEtsyTaxonomysTable
 *
 * @package Etsy\Migrations
 */
class CreateEtsyTaxonomiesTable
{
	public function run(Migrate $migrate, Taxonomy $taxonomy)
	{
		$migrate->createTable('Etsy\Models\Taxonomy');

		/** @var TaxonomyService $taxonomyService */
		$taxonomyService = pluginApp(TaxonomyService::class);
		$taxonomies = $taxonomyService->getSellerTaxonomy('en');

		foreach ($taxonomies['result'] as $data) {
		    $savableArrays = $this->createSavableArray($data);

		    foreach ($savableArrays as $savableArray) {
                $taxonomy->fillByAttributes($savableArray);
            }
        }
	}

	protected function createSavableArray($taxonomy) {
        $result = [];

        $result[] = [
          'id' => $taxonomy['id'],
          'children' => $taxonomy['children'],
          'level' => $taxonomy['level'],
          'name' => $taxonomy['name'],
          'parentId' => $taxonomy['parentId'],
          'path' => $taxonomy['path'],
//todo isLeaf???
        ];

        if (count($taxonomy['children'])) {
            foreach ($taxonomy['children'] as $child) {
                $result[] = $this->createSavableArray($child);
            }
        }

        return $result;
    }
}