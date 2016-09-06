<?hh // strict
namespace Etsy;

use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;
use Etsy\Handler\ItemExportHandler;

class EtsyServiceProvider extends ServiceProvider
{
	public function register():void
	{
        $this->getApplication()->register(\Etsy\EtsyRouteServiceProvider::class);
	}

    public function boot(CronContainer $container):void
    {
        $container->add(CronContainer::DAILY, ItemExportHandler::class);
    }
}
