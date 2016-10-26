<?php
namespace Etsy\Logger;

use Plenty\Plugin\ConfigRepository;

/**
 * Class Logger
 */
class Logger
{
	/**
	 * @var string
	 */
	private $logClient;

	/**
	 * @param ConfigRepository $config
	 */
	public function __construct(ConfigRepository $config)
	{
		$this->logClient = $config->get('logName');
	}

	/**
	 * @param string $message
	 */
	public function log($message)
	{
		// TODO publish log message
	}
}
