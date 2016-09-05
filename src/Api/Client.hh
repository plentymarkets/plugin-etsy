<?hh //strict

namespace Etsy\Api;

use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;

class Client
{
	private LibraryCallContract $library;

    public function __construct(LibraryCallContract $library)
    {
        $this->library = $library;
    }	

    /**
     * Call the etsy API.
     * 
     * @param  string method The method that should be called.
     * @param  ?array<mixed,mixed> params The params that should pe used for the call.
     * @return mixed
     */
    public function call(string $method, ?array<mixed,mixed> $params = []):mixed
    {
    	$response = $this->library->call('EtsyIntegrationPlugin::etsy_sdk', [
            'consumerKey' => 'mmmgsrtdngz5f7h8jlbk81i3', // TODO grab this from config
            'consumerSecret' => '4voazs4ulx', // TODO grab this from config
            'accessToken' => '1e597088a0b49070f12cf5fc8725ec', // TODO grab this from config
            'accessTokenSecret' => '30e33b2cf8', // TODO grab this from config
            'method' => $method,
            'params' => $params,
        ]);
    
        return $response;
    }
}