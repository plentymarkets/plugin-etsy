<?hh // strict

namespace Etsy;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Etsy\Services\Order\OrderImportService;

/**
 * Class EtsyRouteServiceProvider
 */
class EtsyRouteServiceProvider extends RouteServiceProvider
{
	/**
	 * @param Router $router
	 */
	public function map(Router $router):void
	{
		$router->get('etsy-test/order-import', ['middleware' => 'oauth', 'uses' => (OrderImportService $service) ==> {
            $service->run('2016-09-10 00:00:00', date('c'));
        }]);
	}
}
