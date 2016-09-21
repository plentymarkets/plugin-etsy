<?hh //strict

namespace Etsy\Services\Taxonomy;

use Etsy\Logger\Logger;
use Etsy\Api\Client;

class TaxonomyService
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
    public function getSellerTaxonomy(string $language):array<mixed,mixed>
    {
        $response = $this->client->call('getSellerTaxonomy', [
            'language' => $language,
        ]);

        if(is_null($response) || (array_key_exists('exception', $response) && $response['exception'] === true))
        {
            $this->logger->log('Could not get seller taxonomies for language "' . $language  . '". Reason: ...');

            return []; // TODO  throw exception
        }

        $results = $response['results'];

        if(is_array($results))
        {
            return $results;
        }

        return [];
    }
}
