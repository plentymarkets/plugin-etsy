<?hh // strict
namespace Etsy;

use Plenty\Modules\Cron\Services\CronContainer;
use Plenty\Plugin\ServiceProvider;

use Etsy\Contracts\ItemDataProviderContract;
use Etsy\Crons\ItemExportCron;
use Etsy\Crons\ItemUpdateCron;
use Etsy\Crons\OrderImportCron;
use Etsy\Factories\ItemDataProviderFactory;
use Etsy\DataProviders\ItemExportDataProvider;
use Etsy\DataProviders\ItemStockUpdateDataProvider;
use Etsy\DataProviders\TaxonomyDeDataProvider;

use Etsy\Contracts\EtsyTaxonomyRepositoryContract;
use Etsy\Repositories\EtsyTaxonomyRepository;
use Etsy\Contracts\ShippingProfileRepositoryContract;
use Etsy\Repositories\ShippingProfileRepository;

class EtsyServiceProvider extends ServiceProvider
{
	public function register():void
	{
        $this->getApplication()->bind(EtsyTaxonomyRepositoryContract::class, EtsyTaxonomyRepository::class);
        $this->getApplication()->bind(ShippingProfileRepositoryContract::class, ShippingProfileRepository::class);

		$this->getApplication()->bind('Etsy\item.dataprovider.export', ItemExportDataProvider::class);
		$this->getApplication()->bind('Etsy\item.dataprovider.update', ItemStockUpdateDataProvider::class);

        $this->getApplication()->bind('Etsy\taxonomy.dataprovider.de', TaxonomyDeDataProvider::class);

		$this->getApplication()->singleton(ItemDataProviderFactory::class);

        $this->getApplication()->register(EtsyRouteServiceProvider::class);
	}

    public function boot(CronContainer $container):void
    {
        $container->add(CronContainer::DAILY, ItemExportCron::class);
        $container->add(CronContainer::DAILY, ItemUpdateCron::class);
        $container->add(CronContainer::HOURLY, OrderImportCron::class);

    }
}
