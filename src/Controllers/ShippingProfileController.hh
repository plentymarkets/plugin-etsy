<?hh //strict
namespace Etsy\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Etsy\Contracts\ShippingProfileRepositoryContract;

class ShippingProfileController extends Controller
{
    private ShippingProfileRepositoryContract $shippingProfileRepository;

    private Request $request;

    public function __construct(
        ShippingProfileRepositoryContract $shippingProfileRepository,
        Request $request
    )
    {
        $this->shippingProfileRepository = $shippingProfileRepository;
        $this->request = $request;
    }
}
