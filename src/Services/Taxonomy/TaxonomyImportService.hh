<?hh //strict

namespace Etsy\Services\Taxonomy;

use Etsy\Api\Services\TaxonomyService;

class TaxonomyImportService
{
    private TaxonomyService $taxonomyService;

    public function __construct(
        TaxonomyService $taxonomyService
    )
    {
        $this->taxonomyService = $taxonomyService;
    }

    public function run(string $language):void
    {
        $list = [];

        $taxonomies = $this->taxonomyService->getSellerTaxonomy($language);

        $this->generateFile($taxonomies, $language);
    }

    private function generateFile(array<mixed,mixed> $taxonomies, string $language):void
    {
        $contents = '<?hh //strict
namespace Etsy\DataProviders;

use Etsy\Contracts\TaxonomyDataProviderContract;

class Taxonomy' . ucwords($language) . 'DataProvider implements TaxonomyDataProviderContract
{
    public function data():array<int,mixed>
    {
        return ' . var_export($taxonomies, true) . ';
    }
}';
        // save somewhere the generated content
    }
}
