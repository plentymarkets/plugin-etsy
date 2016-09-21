<?hh //strict

namespace Etsy\Contracts;

interface TaxonomyDataProviderContract
{
    public function fetch():array<int,mixed>;
}
