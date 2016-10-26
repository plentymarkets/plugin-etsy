<?php

namespace Etsy\Api;

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
	 * @param LibraryCallContract $library
	 * @param ConfigRepository    $config
	 */
	public function __construct(LibraryCallContract $library, ConfigRepository $config)
	{
		$this->library = $library;
		$this->config  = $config;
	}

	/**
	 * Call the etsy API.
	 * @param  string $method       The method that should be called.
	 * @param  array  $params       The params that should pe used for the call. Eg. /shops/:shop_id/sections/:shop_section_id -> shop_id and shop_section_id are params.
	 * @param  array  $data         The data that should pe used for the post call.
	 * @param  array  $fields       The fields that should be returned.
	 * @param  array  $associations The associations that should be returned.
	 * @param  bool   $sandbox      Default is false.
	 * @return array|null
	 */
	public function call($method, array $params = [], array $data = [], array $fields = [], array $associations = [], $sandbox = false)
	{
		$response = $this->library->call('EtsyIntegrationPlugin::etsy_sdk', [
			'consumerKey'       => $this->config->get('EtsyIntegrationPlugin.consumerKey'),
			'consumerSecret'    => $this->config->get('EtsyIntegrationPlugin.consumerSecret'),
			'accessToken'       => $this->config->get('EtsyIntegrationPlugin.accessToken'),
			'accessTokenSecret' => $this->config->get('EtsyIntegrationPlugin.accessTokenSecret'),
			'sandbox'           => $sandbox,

			'method'       => $method,
			'params'       => $params,
			'data'         => $data,
			'fields'       => $fields,
			'associations' => $associations,
		]);

		return $response;
	}
}
