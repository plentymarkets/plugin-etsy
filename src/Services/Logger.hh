<?hh //strict
namespace Etsy\Services;

use Plenty\Plugin\ConfigRepository;

class Logger
{
	/**
	 * string $logClient
	 */
	private string $logClient;

	/**	 
     * @param ConfigRepository $config
	 */
	public function __construct(ConfigRepository $config)
	{
        $this->logClient = $config->get('logName');
	}

	public function log(string $message):void
	{
		// TODO publish log message
	}
}
