<?hh //strict
namespace Etsy\Contracts;

interface EtsyTaxonomyRepositoryContract
{
    public function findById(int $taxonomyId, string $language):?array<string,mixed>;

    public function all(int $taxonomyId, string $language):?array<int,mixed>;
}
