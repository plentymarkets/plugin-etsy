<?hh //strict
namespace Etsy\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Etsy\Contracts\EtsyTaxonomyRepositoryContract;

class TaxonomyController extends Controller
{
    private EtsyTaxonomyRepositoryContract $etsyTaxonomyRepository;

    private Request $request;

    public function __construct(
        EtsyTaxonomyRepositoryContract $etsyTaxonomyRepository,
        Request $request
    )
    {
        $this->etsyTaxonomyRepository = $etsyTaxonomyRepository;
        $this->request = $request;
    }

    public function showEtsyTaxonomy(int $id):?array<string,mixed>
    {
        $taxonomy = $this->etsyTaxonomyRepository->findById($id, (string) $this->request->get('language', 'de'));

        return $taxonomy;
    }

    public function allEtsyTaxonomies(int $id):?array<int,mixed>
    {
        $taxonomies = $this->etsyTaxonomyRepository->all($id, (string) $this->request->get('language', 'de'));

        return $taxonomies;
    }
}
