<?hh // strict
namespace Etsy;

use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;
use Etsy\Handlers\EtsySendMailHandler;

class EtsyServiceProvider extends ServiceProvider
{
	public function boot(CronContainer $crons):void
	{
		$crons->add(CronContainer::HOURLY, EtsySendMailHandler::class);
	}

	public function register():void
	{
		//
	}	
}
