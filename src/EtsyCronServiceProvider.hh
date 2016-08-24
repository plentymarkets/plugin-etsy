<?hh // strict
namespace Etsy;

use Plenty\Plugin\CronServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;
use Etsy\Handlers\EtsySendMailHandler;

class EtsyCronServiceProvider extends CronServiceProvider
{
	public function register():void
	{
	}

	public function crons(CronContainer $container):void
	{
		$container->add(15, 'EtsySendMail', EtsySendMailHandler::class);
	}
}