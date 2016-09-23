<?hh //strict
namespace Etsy\Contracts;

use Etsy\Models\ShippingProfile;

interface ShippingProfileRepositoryContract
{
    public function create(array<string,mixed> $data):void;

    public function all():void;
}
