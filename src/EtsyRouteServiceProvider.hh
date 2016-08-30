<?hh //strict

namespace Etsy;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\ApiRouter;
/**
 * Class EtsyRouteServiceProvider
 *
 * @author mbrueschke
 * @package Etsy
 */
class EtsyRouteServiceProvider extends RouteServiceProvider
{

    /**
     *
     */
    public function register():void
    {
    }

    /**
     * @param ApiRouter $route
     */
    public function map(ApiRouter $route):void
    {
        $route->version(['v1'], ['namespace' => 'Etsy'], (ApiRouter $route) ==> {
        $route->resource('etsy/category', 'Controller\RestController@getCategory');
        });
    }
}