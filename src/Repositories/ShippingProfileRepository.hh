<?hh //strict
namespace Etsy\Repositories;

use Etsy\Contracts\ShippingProfileRepositoryContract;
use Etsy\Models\ShippingProfile;
use Etsy\Storage\ShippingProfileStorage;

class ShippingProfileRepository implements ShippingProfileRepositoryContract
{
    private ShippingProfileStorage $storage;

    private ShippingProfile $model;

    public function __construct(
        ShippingProfileStorage $storage,
        ShippingProfile $model
    )
    {
        $this->storage = $storage;
        $this->model = $model;
    }

    public function create(array<string,mixed> $data):void
    {
        $this->storage->post($data);
    }

    public function all():void
    {

    }
}
