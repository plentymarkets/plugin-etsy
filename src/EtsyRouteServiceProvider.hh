<?hh // strict

namespace Etsy;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Etsy\Services\Order\OrderImportService;
use Etsy\Batch\Item\ItemExportService;
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

        $router->get('etsy/taxonomies/{id}', ['middleware' => 'oauth', 'uses' => 'Etsy\Controllers\TaxonomyController@showEtsyTaxonomy']);
        $router->get('etsy/taxonomies', ['middleware' => 'oauth', 'uses' => 'Etsy\Controllers\TaxonomyController@allEtsyTaxonomies']);
	}
}
