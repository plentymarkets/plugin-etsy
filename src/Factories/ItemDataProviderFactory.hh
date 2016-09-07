<?hh //strict

namespace Etsy\Factories;

use Plenty\Plugin\Application;
use Etsy\Contracts\ItemDataProviderContract;

class ItemDataProviderFactory
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
	public function make(string $type):ItemDataProviderContract
	{
		$provider = $this->app->make('Etsy\item.dataprovider.'.$type);

		invariant($provider instanceof ItemDataProviderContract, 'Unknown data provider');

		return $provider;
	}
}