<?hh //strict
namespace Etsy\Services\Item;

use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Helper\ItemHelper;
use Etsy\Api\Client;
use Etsy\Logger\Logger;
use Etsy\Services\Item\ListingImageService;

class StartListingService
{
    /**
    * ConfigRepository $config
    */
    private ConfigRepository $config;

    /**
    * ItemHelper $itemHelper
    */
    private ItemHelper $itemHelper;

    /**
    * Client $client
    */
    private Client $client;

    /**
    * Logger $logger
    */
    private Logger $logger;

    /**
    * ListingImageService $imageService;
    */
    private ListingImageService $imageService;

    public function __construct(
        ItemHelper $itemHelper,
        ConfigRepository $config,
        ListingImageService $imageService,
        Client $client,
        Logger $logger,
    )
    {
        $this->itemHelper = $itemHelper;
        $this->config = $config;
        $this->client = $client;
        $this->logger = $logger;
        $this->imageService = $imageService;
    }

    public function start(Record $record):void
    {
        // $listingId = $this->createListing($record);

        $listingId = $this->createListingMockupResponse();

        if(!is_null($listingId))
        {
            // $this->addPictures($record, $listingId);

            // $this->addVariations($record, $group);

            $this->addTranslations($record);

            $this->publish();
        }
        else
        {
            $this->logger->log('Could not start listing for variation id: ' . $record->itemBase->id);
        }

    }

    private function primaryVariation(array<int,Record> $group):Record
    {
        return reset($group);
    }

    private function createListing(Record $primaryVariation):?int
    {
        $itemData = [
            'state'                 => 'draft',
            'title'                 => 'Test', // get title
            'description'           => 'Description', // get description
            'quantity'              => $this->itemHelper->getStock($primaryVariation),
            'price'                 => number_format($primaryVariation->variationRetailPrice->price, 2),
            'shipping_template_id'  => $this->itemHelper->getItemProperty($primaryVariation, 'shipping_template_id'),
            'who_made'              => $this->itemHelper->getItemProperty($primaryVariation, 'who_made'),
            'is_supply'             => (string) $this->itemHelper->getItemProperty($primaryVariation, 'is_supply'),
            'when_made'             => $this->itemHelper->getItemProperty($primaryVariation, 'when_made')
        ];

        $response = $this->client->call('createListing', ['language' => 'de'], $itemData);

        if(is_null($response) || (array_key_exists('exception', $response) && $response['exception'] === true))
        {
            return null; // TODO  throw exception
        }

        return (int) reset($response['results'])['listing_id'];
    }

    private function addPictures(Record $primaryVariation, int $listingId):void
    {
        $list = $this->itemHelper->getImageList($primaryVariation->variationImageList['all_images']->toArray(), 'normal');

        foreach($list as $image)
        {
            $this->imageService->uploadListingImage($listingId, $image);
        }
    }

    private function addVariations(Record $primaryVariation, array<int,Record> $group):void
    {

    }



    private function addTranslations(Record $primaryVariation):void
    {

    }

    private function publish():void
    {

    }




    private function createListingMockupResponse():int
    {
        return 465738294;
    }
}
