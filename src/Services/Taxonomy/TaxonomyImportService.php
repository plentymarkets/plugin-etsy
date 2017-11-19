<?php

namespace Etsy\Services\Taxonomy;

use Etsy\Api\Services\TaxonomyService;

/**
 * Class TaxonomyImportService
 */
class TaxonomyImportService
{
	/**
	 * @var TaxonomyService
	 */
	private $taxonomyService;

	/**
	 * @param TaxonomyService $taxonomyService
	 */
	public function __construct(TaxonomyService $taxonomyService)
	{
		$this->taxonomyService = $taxonomyService;
	}

	/**
	 * @param string $language
	 */
	public function run($language)
	{
		$taxonomies = $this->taxonomyService->getSellerTaxonomy($language);

		$this->generateFile($taxonomies, $language);
	}

	/**
	 * @param array  $taxonomies
	 * @param string $language
	 */
	private function generateFile(array $taxonomies, $language)
	{
		$contents = '<?php
namespace Etsy\DataProviders;

use Etsy\Contracts\TaxonomyDataProviderContract;

class Taxonomy' . ucwords($language) . 'DataProvider implements TaxonomyDataProviderContract
{
    public function data():array
    {
        return null;
    }
}';
		// save somewhere the generated content
	}
}
