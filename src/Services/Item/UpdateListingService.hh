<?hh //strict
namespace Etsy\Services\Item;

use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Api\Services\ListingService;
use Etsy\Helper\ItemHelper;
use Etsy\Logger\Logger;


class UpdateListingService
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


    public function __construct(
        ItemHelper $itemHelper,
        ConfigRepository $config,
        ListingService $listingService,
        Logger $logger,
    )
    {
        $this->itemHelper = $itemHelper;
        $this->config = $config;
        $this->logger = $logger;
        $this->listingService = $listingService;
    }

    public function update(Record $record):void
    {
        if(strlen((string)$record->variationMarketStatus->sku) > 0)
        {
            $listingId = $record->variationMarketStatus->sku;
        }

        $listingId = $this->createListingMockupResponse();  //TODO need to be deleted

        if(!is_null($listingId))
        {
            $this->updateListing($record, $listingId);
        }
        else
        {
            $this->logger->log('Could not start listing for variation id: ' . $record->variationBase->id);
        }

    }

    /**
     * @param Record $record
     * @param int $listingId
    */
    private function updateListing(Record $record, int $listingId):void
    {
        $data = [
            'listing_id' => $listingId,
            'quantity'   => $this->itemHelper->getStock($record),
            'price'      => $record->variationRetailPrice->price
        ];

        $this->listingService->updateListing($listingId, $data);
    }

    private function createListingMockupResponse():int
    {
        return 465564444;
    }
}
