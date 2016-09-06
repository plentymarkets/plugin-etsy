<?hh // strict
namespace Etsy;

use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;
use Etsy\Handlers\ItemExportHandler;
use Etsy\Contracts\ItemDataProviderContract;
use Etsy\Providers\ItemExportDataProvider;

class EtsyServiceProvider extends ServiceProvider
{
	public function register():void
	{
		$this->getApplication()->bind(ItemDataProviderContract::class, ItemExportDataProvider::class); 
	}

    public function boot(CronContainer $container):void
    {
        $container->add(CronContainer::DAILY, ItemExportHandler::class);
    }
}
