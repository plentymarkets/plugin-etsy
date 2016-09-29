<?hh //strict
namespace Etsy\Services\Item;

use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Api\Services\ListingService;
use Etsy\Api\Services\ListingImageService;
use Etsy\Helper\ItemHelper;
use Etsy\Logger\Logger;


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
    * Logger $logger
    */
    private Logger $logger;

    /**
    * ListingService $listingService
    */
    private ListingService $listingService;

    /**
    * ListingImageService $imageService;
    */
    private ListingImageService $listingImageService;

    public function __construct(
        ItemHelper $itemHelper,
        ConfigRepository $config,
        ListingService $listingService,
        ListingImageService $listingImageService,
        Logger $logger
    )
    {
        $this->itemHelper = $itemHelper;
        $this->config = $config;
        $this->logger = $logger;
        $this->listingService = $listingService;
        $this->listingImageService = $listingImageService;
    }

    public function start(Record $record):void
    {
        if(strlen((string)$record->variationMarketStatus->sku) == 0)
        {
            $listingId = $this->createListing($record);
        }
        else
        {
            $listingId = $record->variationMarketStatus->sku;
        }

        $listingId = $this->createListingMockupResponse();

        if(!is_null($listingId))
        {
            // $this->addPictures($record, $listingId);

            // $this->addTranslations($record);

            $this->publish($listingId, $record->variationBase->id);
        }
        else
        {
            $this->logger->log('Could not start listing for variation id: ' . $record->variationBase->id);
        }

    }
    //TODO need new method updateListing if the listing id already exists

    private function createListing(Record $record):?int
    {
        $data = [
            'state'                 => 'inactive',
            'title'                 => 'Test', // get title
            'description'           => 'Description', // get description
            'quantity'              => $this->itemHelper->getStock($record),
            'price'                 => number_format($record->variationRetailPrice->price, 2),
            'shipping_template_id'  => $this->itemHelper->getItemProperty($record, 'shipping_template_id'),
            'who_made'              => $this->itemHelper->getItemProperty($record, 'who_made'),
            'is_supply'             => (string) $this->itemHelper->getItemProperty($record, 'is_supply'),
            'when_made'             => $this->itemHelper->getItemProperty($record, 'when_made'),
            'taxonomy_id' => '',
            'should_auto_renew' => false,
            'is_digital' => false
        ];

        return $this->listingService->createListing('de', $data); // TODO replace all languages with the shop language
    }

    //TODO we need to write an extra method for the update
    private function addPictures(Record $record, int $listingId):void
    {
        $list = $this->itemHelper->getImageList($record->variationImageList['all_images']->toArray(), 'normal');

        foreach($list as $image)
        {
            $this->listingImageService->uploadListingImage($listingId, $image);
        }
    }

    //TODO we need to write an extra method for the update
    private function addTranslations(Record $record):void
    {

    }

    private function publish(int $listingId, int $variationId):void
    {
        $data = [
            'state' => 'active',
        ];

        $response = $this->listingService->updateListing($listingId, $data);

        if($response)
        {
            $this->itemHelper->generateSku($listingId, $variationId);
        }
        else
        {
            // TODO throw exception
        }
    }

    private function createListingMockupResponse():int
    {
        return 465564444;
    }
}
