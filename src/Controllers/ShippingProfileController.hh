<?hh //strict
namespace Etsy\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Etsy\Contracts\ShippingProfileRepositoryContract;
use Etsy\Services\Shipping\ShippingProfileImportService;

class ShippingProfileController extends Controller
{
    private ShippingProfileRepositoryContract $shippingProfileRepository;

    private ShippingProfileImportService $service;

    private Request $request;

    public function __construct(
        ShippingProfileRepositoryContract $shippingProfileRepository,
        Request $request,
        ShippingProfileImportService $service
    )
    {
        $this->shippingProfileRepository = $shippingProfileRepository;
        $this->request = $request;
        $this->service = $service;
    }

    public function importShippingProfiles():void
    {
        $this->service->run();
    }

    public function confirm()
    {
        http://master.plentymarkets.com/biltly-shortner/confirm&code=


        $code = $this->request->get('code');

        $this->httpClient->call('/oauth/access_token', ['code' => 123, 'client_id' => $config])
        $x = 1;
        $this->dynamoDB->create('bilty_token', $token);
    }
}
