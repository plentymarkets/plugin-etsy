<?hh //strict
namespace Etsy\Services\Item;

use Etsy\Api\Client;
use Etsy\Logger\Logger;

class ListingImageService
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

    public function uploadListingImage(int $listingId, string $image):void
    {
        $data = [
            'image' => 'http://testmag.co.uk/wp-content/uploads/2011/06/TEST-PRESENTS.jpg', // TODO replace $image
        ];

        $response = $this->client->call('uploadListingImage', [
            'listing_id' => $listingId,
        ], $data);

        if(is_null($response) || (array_key_exists('exception', $response) && $response['exception'] === true))
        {
            $this->logger->log('Could not upload image for listing id ' . $listingId  . '. Reason: ...');
            // TODO  throw exception
        }
    }
}
