<?hh //strict
namespace Etsy\Api\Services;

use Etsy\Logger\Logger;
use Etsy\Api\Client;

class ShippingTemplateService
{
    private Client $client;

    private Logger $logger;

    public function __construct(
        Client $client,
        Logger $logger
    )
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @return array<mixed>
     */
    public function getShippingTemplate(int $id, string $language):array<mixed,mixed>
    {
        $response = $this->client->call('getShippingTemplate', [
            'language' => $language,
            'shipping_template_id' => $id,
        ],
        [],
        [],
        [
            'Entries' => 'Entries',
            'Upgrades' => 'Upgrades',
        ], true);

        if(is_null($response) || (array_key_exists('exception', $response) && $response['exception'] === true))
        {
            $this->logger->log('Could not get shipping template id "' . $id . '" for language "' . $language  . '". Reason: ...');

            return []; // TODO  throw exception
        }

        $results = $response['results'];

        if(is_array($results))
        {
            return reset($results);
        }

        return [];
    }
}
