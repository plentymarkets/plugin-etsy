<?hh //strict
namespace Etsy\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Etsy\Contracts\EtsyTaxonomyRepositoryContract;
use Etsy\Factories\TaxonomyDataProviderFactory;

class EtsyTaxonomyRepository implements EtsyTaxonomyRepositoryContract
{
    private TaxonomyDataProviderFactory $factory;

    public function __construct(TaxonomyDataProviderFactory $factory)
    {
        $this->factory = $factory;
    }

    public function all(int $taxonomyId, string $language):?array<int,mixed>
    {
        $taxonomyDataProvider = $this->factory->make($language);

        $taxonomies = $taxonomyDataProvider->fetch();

        return $this->getAllTaxonomies($taxonomies);
    }

    private function getAllTaxonomies(array<int,mixed> $taxonomies):array<int,mixed>
    {
        $list = [];

        foreach($taxonomies as $taxonomy)
        {
            if(is_array($taxonomy))
            {
                $taxonomyData = [
                    'id' => $taxonomy['id'],
                    'name' => $taxonomy['name'],
                    'level' => $taxonomy['level'],
                    'parentId' => (int) $taxonomy['parent_id'],
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

    public function findById(int $taxonomyId, string $language):?array<string,mixed>
    {
        $taxonomyDataProvider = $this->factory->make($language);

        $taxonomies = $taxonomyDataProvider->fetch();

        return $this->searchByTaxonomyId($taxonomyId, $taxonomies);
    }

    private function searchByTaxonomyId(int $taxonomyId, array<int,mixed> $taxonomies):?array<string,mixed>
    {
        foreach($taxonomies as $taxonomy)
        {
            $foundTaxonomy = null;

            if(is_array($taxonomy))
            {
                if($taxonomy['id'] == $taxonomyId)
                {
                    $foundTaxonomy = [
                        'id' => $taxonomy['id'],
                        'name' => $taxonomy['name'],
                        'level' => $taxonomy['level'],
                        'parentId' => (int) $taxonomy['parent_id'],
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
