<?hh // strict
namespace Etsy;

use Etsy\Handler\OrderImportHandler;
use Plenty\Modules\Cron\Services\CronContainer;
use Plenty\Plugin\ServiceProvider;

class EtsyServiceProvider extends ServiceProvider
{
	public function register():void
	{
	}

	public function boot(CronContainer $container):void
	{
		$container->add(CronContainer::EVERY_FIFTEEN_MINUTES, OrderImportHandler::class);
	}
}
