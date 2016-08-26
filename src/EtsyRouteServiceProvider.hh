<?hh //strict

namespace Etsy;


use Plenty\Plugin\Routing\Router;
use Plenty\Plugin\RouteServiceProvider;
//use Illuminate\Routing\Router as WebRouter;
//use Plenty\Api\Services\VersionHandling;

/**
 * Class EtsyRouteServiceProvider
 *
 * @author mbrueschke
 * @package
 */
class EtsyRouteServiceProvider extends RouteServiceProvider
{
//    /**
//     * @param WebRouter $router
//     */
//    public function boot(WebRouter $router):void
//    {
//        parent::boot($router);
//    }

    /**
     *
     */
    public function register():void
    {
    }

    public function map(Router $route):void
    {
//        $route->version(VersionHandling::startingFrom('v1'), ['namespace' => 'Category;', 'middleware' => ['oauth']],
            $route->get('etsy/category', 'RestController@getCategory');
    }
}