<?php //strict
namespace Etsy\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Etsy\Contracts\EtsyTaxonomyRepositoryContract;

class TaxonomyController extends Controller
{
    /**
    * @var EtsyTaxonomyRepositoryContract 
    */
  private $etsyTaxonomyRepository;

    /**
    * @var Request 
    */
  private $request;

    public function __construct(
        EtsyTaxonomyRepositoryContract $etsyTaxonomyRepository,
        Request $request
    )
    {
        $this->etsyTaxonomyRepository = $etsyTaxonomyRepository;
        $this->request = $request;
    }

	/**
	 * @param int $id
	 * @return array
	 */
    public function showEtsyTaxonomy($id)
    {
        $taxonomy = $this->etsyTaxonomyRepository->findById($id, (string) $this->request->get('language', 'de'));

        return $taxonomy;
    }

	/**
	 * @param int $id
	 * @return array
	 */
    public function allEtsyTaxonomies($id)
    {
        $taxonomies = $this->etsyTaxonomyRepository->all($id, (string) $this->request->get('language', 'de'));

        return $taxonomies;
    }
}
