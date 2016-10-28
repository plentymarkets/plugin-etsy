<?php
namespace Etsy;

use Plenty\Modules\Cron\Services\CronContainer;
use Plenty\Plugin\ServiceProvider;

use Etsy\Crons\ItemExportCron;
use Etsy\Crons\ItemUpdateCron;
use Etsy\Crons\OrderImportCron;
use Etsy\Factories\ItemDataProviderFactory;
use Etsy\DataProviders\ItemExportDataProvider;
use Etsy\DataProviders\ItemUpdateDataProvider;
use Etsy\DataProviders\TaxonomyDeDataProvider;
use Etsy\Contracts\EtsyTaxonomyRepositoryContract;
use Etsy\Repositories\EtsyTaxonomyRepository;
use Etsy\Contracts\ShippingProfileRepositoryContract;
use Etsy\Repositories\ShippingProfileRepository;

/**
 * Class EtsyServiceProvider
 */
class EtsyServiceProvider extends ServiceProvider
{
	/**
	 * @return void
	 */
	public function register()
	{
		$this->getApplication()->bind(EtsyTaxonomyRepositoryContract::class, EtsyTaxonomyRepository::class);
		$this->getApplication()->bind(ShippingProfileRepositoryContract::class, ShippingProfileRepository::class);

		$this->getApplication()->bind('Etsy\item.dataprovider.export', ItemExportDataProvider::class);
		$this->getApplication()->bind('Etsy\item.dataprovider.update', ItemUpdateDataProvider::class);

		$this->getApplication()->bind('Etsy\taxonomy.dataprovider.de', TaxonomyDeDataProvider::class);

		$this->getApplication()->singleton(ItemDataProviderFactory::class);

		$this->getApplication()->register(EtsyRouteServiceProvider::class);
	}

	/**
	 * @param CronContainer          $container
	 */
	public function boot(CronContainer $container)
	{
		// register crons
		$container->add(CronContainer::DAILY, ItemExportCron::class);
		$container->add(CronContainer::DAILY, ItemUpdateCron::class);
		$container->add(CronContainer::HOURLY, OrderImportCron::class);
	}
}
