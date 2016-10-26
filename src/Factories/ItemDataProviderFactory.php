<?php

namespace Etsy\Factories;

use Plenty\Plugin\Application;
use Etsy\Contracts\ItemDataProviderContract;

/**
 * Class ItemDataProviderFactory
 */
class ItemDataProviderFactory
{
	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Get the designated data provider.
	 *
	 * @param  string $type
	 * @return ItemDataProviderContract|null
	 */
	public function make($type)
	{
		$provider = $this->app->make('Etsy\item.dataprovider.' . $type);

		if(!$provider instanceof ItemDataProviderContract)
		{
			return null;
		}

		return $provider;
	}
}