<?hh //strict
namespace Etsy\Api\Services;

use Etsy\Api\Client;
use Etsy\Logger\Logger;

class ListingService
{
    /**
    * Client $client
    */
    private Client $client;

    /**
    * Logger $logger
    */
    private Logger $logger;

    public function __construct(
        Client $client,
        Logger $logger
    )
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function createListing(string $language, array<string,mixed> $data):?int
    {
        $response = $this->client->call('createListing', ['language' => 'de'], $data);

        if(is_null($response) || (array_key_exists('exception', $response) && $response['exception'] === true))
        {
            return null; // TODO  throw exception
        }

        return (int) reset($response['results'])['listing_id']; // TODO maybe it's better to return the entire listing data?
    }

    public function updateListing(int $id, array<string,mixed> $data):bool
    {
        $response = $this->client->call('updateListing', [
            'listing_id' => $id,
        ], $data);

        if(is_null($response) || (array_key_exists('exception', $response) && $response['exception'] === true))
        {
            // TODO  throw exception
            return false;
        }

        return true;
    }
}
