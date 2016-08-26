<?hh // strict
namespace Etsy;

use Plenty\Plugin\ServiceProvider;

class EtsyServiceProvider extends ServiceProvider
{
	public function register():void
	{
        $this->getApplication()->register(\Etsy\EtsyRouteServiceProvider::class);
	}	
}
