<?hh //strict

namespace Etsy\Factories;

use Plenty\Plugin\Application;
use Etsy\Contracts\TaxonomyDataProviderContract;

class TaxonomyDataProviderFactory
{
	/**
	 * Application $app
	 */
	private Application $app;

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
	 * @return ItemDataProviderContract
	 */
	public function make(string $type):TaxonomyDataProviderContract
	{
		$provider = $this->app->make('Etsy\taxonomy.dataprovider.'.$type);

		invariant($provider instanceof TaxonomyDataProviderContract, 'Unknown data provider');

		return $provider;
	}
}
