<?php

namespace Etsy\Api;

use Etsy\Helper\AccountHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;

/**
 * Class Client
 */
class Client
{
	/**
	 * @var LibraryCallContract
	 */
	private $library;

	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @var AccountHelper
	 */
	private $accountHelper;

	/**
	 * @param LibraryCallContract $library
	 * @param ConfigRepository    $config
	 * @param AccountHelper       $accountHelper
	 */
	public function __construct(LibraryCallContract $library, ConfigRepository $config, AccountHelper $accountHelper)
	{
		$this->library       = $library;
		$this->config        = $config;
		$this->accountHelper = $accountHelper;
	}

    /**
     * Call the etsy API.
     *
     * @param string $method The method that should be called.
     * @param array $params The params that should pe used for the call. Eg. /shops/:shop_id/sections/:shop_section_id -> shop_id and shop_section_id are params.
     * @param array $data The data that should pe used for the post call.
     * @param array $fields The fields that should be returned.
     * @param array $associations The associations that should be returned.
     * @param bool $sandbox Default is false.
     * @param int $retries
     * @return array
     * @throws \Exception
     */
	public function call($method, array $params = [], array $data = [], array $fields = [], array $associations = [], $sandbox = false, int $retries = 3)
	{
		$tokenData = $this->accountHelper->getTokenData();

        $response = $this->library->call(SettingsHelper::PLUGIN_NAME . '::etsy_sdk', [
            'consumerKey'       => $this->accountHelper->getConsumerKey(),
            'consumerSecret'    => $this->accountHelper->getConsumerSecret(),
            'accessToken'       => $tokenData['accessToken'],
            'accessTokenSecret' => $tokenData['accessTokenSecret'],
            'sandbox'           => $sandbox,

            'method'       => $method,
            'params'       => $params,
            'data'         => $data,
            'fields'       => $fields,
            'associations' => $associations,
        ]);

        // exception in the response means that something inside the sdk that tried to communicate with etsy failed
        if ((isset($response['exception']) && $response['exception'] == true)) {
            if (!isset($response['message'])) {
                if ($retries > 0) {
                    sleep(1);
                    return $this->call($method, $params, $data, $fields, $associations, $sandbox, $retries - 1);
                }

                throw new \Exception("Could not establish connection to Etsy.");
            }

            throw new \Exception($response['message']);
        }

        // error in response means, that something regarding the communication with the sdk server failed
        if ((isset($response['error']) && $response['error'] == true)) {
            if ($retries > 0) {
                sleep(1);
                return $this->call($method, $params, $data, $fields, $associations, $sandbox, $retries - 1);
            }
            
            throw new \Exception("Error: " . json_encode($response));
        }

		return $response;
	}
}
