<?php

namespace Etsy\Repositories;

use Etsy\Contracts\EtsyTaxonomyRepositoryContract;
use Etsy\Factories\TaxonomyDataProviderFactory;

/**
 * Class EtsyTaxonomyRepository
 */
class EtsyTaxonomyRepository implements EtsyTaxonomyRepositoryContract
{
	/**
	 * @var TaxonomyDataProviderFactory
	 */
	private $factory;

	/**
	 * @param TaxonomyDataProviderFactory $factory
	 */
	public function __construct(TaxonomyDataProviderFactory $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * @param int    $taxonomyId
	 * @param string $language
	 * @return array
	 */
	public function all($taxonomyId, $language)
	{
		$taxonomyDataProvider = $this->factory->make($language);

		$taxonomies = $taxonomyDataProvider->fetch();

		return $this->getAllTaxonomies($taxonomies);
	}

	/**
	 * @param array $taxonomies
	 * @return array
	 */
	private function getAllTaxonomies(array $taxonomies)
	{
		$list = [];

		foreach($taxonomies as $taxonomy)
		{
			if(is_array($taxonomy))
			{
				$taxonomyData = [
					'id'         => $taxonomy['id'],
					'name'       => $taxonomy['name'],
					'level'      => $taxonomy['level'],
					'parentId'   => (int) $taxonomy['parent_id'],
					'categoryId' => (int) $taxonomy['category_id'],
				];

				if(array_key_exists('children', $taxonomy))
				{
					$taxonomyData['children'] = $this->getAllTaxonomies($taxonomy['children']);
				}

				$list[] = $taxonomyData;
			}
		}

		return $list;
	}

	/**
	 * @param int    $taxonomyId
	 * @param string $language
	 * @return array
	 */
	public function findById($taxonomyId, $language)
	{
		$taxonomyDataProvider = $this->factory->make($language);

		$taxonomies = $taxonomyDataProvider->fetch();

		return $this->searchByTaxonomyId($taxonomyId, $taxonomies);
	}

	/**
	 * @param int   $taxonomyId
	 * @param array $taxonomies
	 * @return array
	 */
	private function searchByTaxonomyId($taxonomyId, array $taxonomies)
	{
		foreach($taxonomies as $taxonomy)
		{
			$foundTaxonomy = null;

			if(is_array($taxonomy))
			{
				if($taxonomy['id'] == $taxonomyId)
				{
					$foundTaxonomy = [
						'id'         => $taxonomy['id'],
						'name'       => $taxonomy['name'],
						'level'      => $taxonomy['level'],
						'parentId'   => (int) $taxonomy['parent_id'],
						'categoryId' => (int) $taxonomy['category_id'],
					];
				}
				elseif(array_key_exists('children', $taxonomy))
				{
					$foundTaxonomy = $this->searchByTaxonomyId($taxonomyId, $taxonomy['children']);
				}

				if(!is_null($foundTaxonomy))
				{
					return $foundTaxonomy;
				}
			}
		}

		return null;
	}
}
