<?hh //strict
namespace Etsy\Repositories;

use Etsy\Contracts\ShippingProfileRepositoryContract;
use Etsy\Storage\ShippingProfileStorage;

class ShippingProfileRepository implements ShippingProfileRepositoryContract
{
    private ShippingProfileStorage $storage;

    public function __construct(ShippingProfileStorage $storage)
    {
        $this->storage = $storage;        
    }

    public function create(array<string,mixed> $data):void
    {
        $this->storage->post($data);
    }

    public function all():void
    {

    }
}
