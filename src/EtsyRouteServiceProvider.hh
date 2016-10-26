<?hh // strict

namespace Etsy;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Etsy\Services\Order\OrderImportService;
use Etsy\Batch\Item\ItemExportService;
use Etsy\Batch\Item\ItemUpdateService;
use Etsy\Services\Shipping\ShippingProfileImportService;
use Etsy\Services\Taxonomy\TaxonomyImportService;

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

        $router->get('etsy-test/item-export', ['middleware' => 'oauth', 'uses' => (ItemExportService $service) ==> {
            $service->run();
        }]);

        $router->get('etsy-test/item-update', ['middleware' => 'oauth', 'uses' => (ItemUpdateService $service) ==> {
        $service->run();
        }]);

        $router->get('etsy-test/taxonomies-import', ['middleware' => 'oauth', 'uses' => (TaxonomyImportService $service) ==> {
            $service->run('de');
            $service->run('en');
            $service->run('es');
            $service->run('fr');
            $service->run('it');
            $service->run('ja');
            $service->run('pt');
            $service->run('ru');
        }]);

        $router->get('etsy-shortner/confirm/{clientId}/{url}', ['as' => 'bilty.shortner.get', 'uses' => 'AuthController@confirm']);

        {{ route('bitly.shortner.get', ['clientId' => 123, 'url' => 12313]) }}

        $router->get('etsy/taxonomies/{id}', ['middleware' => 'oauth', 'uses' => 'Etsy\Controllers\TaxonomyController@showEtsyTaxonomy']);
        $router->get('etsy/taxonomies', ['middleware' => 'oauth', 'uses' => 'Etsy\Controllers\TaxonomyController@allEtsyTaxonomies']); // TODO save

        $router->get('etsy/settings/shipping-profiles/import', ['middleware' => 'oauth', 'uses' => 'Etsy\Controllers\ShippingProfileController@importShippingProfiles']);
	}
}
