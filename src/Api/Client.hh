<?hh //strict

namespace Etsy\Api;

use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;

class Client
{
    /**
     * LibraryCallContract $library
     */
	private LibraryCallContract $library;

    /**
     * ConfigRepository $config
     */
    private ConfigRepository $config;

    public function __construct(LibraryCallContract $library, ConfigRepository $config)
    {
        $this->library = $library;
        $this->config = $config;
    }	

    /**
     * Call the etsy API.
     * 
     * @param  string $method The method that should be called.
     * @param  ?array<string,mixed> $params The params that should pe used for the call. Eg. /shops/:shop_id/sections/:shop_section_id -> shop_id and shop_section_id are params.
     * @param  ?array<string,mixed> $data The data that should pe used for the post call. 
     * @param  ?array<string,mixed> $fields The fields that should be returned.
     * @param  ?array<string,mixed> $associations The associations that should be returned.
     * @return array<string,mixed>
     */
    public function call(string $method, ?array<string,mixed> $params = [], ?array<string,mixed> $data = [], ?array<string, mixed> $fields, ?array<string, mixed> $associations):array<string,mixed>
    {
    	$response = $this->library->call('EtsyIntegrationPlugin::etsy_sdk', [
            'consumerKey' => $this->config->get('EtsyIntegrationPlugin.consumerKey'),
            'consumerSecret' => $this->config->get('EtsyIntegrationPlugin.consumerSecret'),
            'accessToken' => $this->config->get('EtsyIntegrationPlugin.accessToken'),
            'accessTokenSecret' => $this->config->get('EtsyIntegrationPlugin.accessTokenSecret'),

            'method' => $method,
            'params' => $params,
            'data' => $data,
            'fields' => $fields,
            'associations' => $associations,
        ]);
    
        return $response;
    }
}