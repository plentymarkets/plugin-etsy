<?hh //strict
namespace Etsy\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingProfile extends Model
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}
