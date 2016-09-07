<?hh // strict
namespace Etsy;

use Etsy\Handler\OrderImportHandler;
use Plenty\Modules\Cron\Services\CronContainer;
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;

use Etsy\Contracts\ItemDataProviderContract;
use Etsy\Crons\ItemExportCron;
use Etsy\Crons\ItemUpdateCron;
use Etsy\Factories\ItemDataProviderFactory;
use Etsy\Providers\ItemExportDataProvider;
use Etsy\Providers\ItemUpdateDataProvider;

class EtsyServiceProvider extends ServiceProvider
{
	public function register():void
	{
		$this->getApplication()->bind('Etsy\item.dataprovider.export', ItemExportDataProvider::class);
		$this->getApplication()->bind('Etsy\item.dataprovider.update', ItemUpdateDataProvider::class);

		$this->getApplication()->singleton(ItemDataProviderFactory::class);
	}

    public function boot(CronContainer $container):void
    {
        $container->add(CronContainer::DAILY, ItemExportCron::class);
        $container->add(CronContainer::DAILY, ItemUpdateCron::class);
    }
}
