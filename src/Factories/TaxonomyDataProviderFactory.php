<?php

namespace Etsy\Factories;

use Plenty\Plugin\Application;
use Etsy\Contracts\TaxonomyDataProviderContract;

/**
 * Class TaxonomyDataProviderFactory
 */
class TaxonomyDataProviderFactory
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
	 * @return TaxonomyDataProviderContract
	 */
	public function make($type)
	{
		$provider = $this->app->make('Etsy\taxonomy.dataprovider.' . $type);

		if(!$provider instanceof TaxonomyDataProviderContract)
		{
			return null;
		}

		return $provider;
	}
}
