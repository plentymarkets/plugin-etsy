<?hh //strict
namespace Etsy\Services\Item;

use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Api\Services\ListingService;
use Etsy\Api\Services\ListingImageService;
use Etsy\Helper\ItemHelper;
use Etsy\Logger\Logger;
use Etsy\Api\Services\ListingTranslationService;


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

    /**
     * ListingTranslationService $listingTranslationService
     */
    private ListingTranslationService $listingTranslationService;

    public function __construct(
        ItemHelper $itemHelper,
        ConfigRepository $config,
        ListingService $listingService,
        ListingImageService $listingImageService,
        Logger $logger,
        ListingTranslationService $listingTranslationService
    )
    {
        $this->itemHelper = $itemHelper;
        $this->config = $config;
        $this->logger = $logger;
        $this->listingTranslationService = $listingTranslationService;
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

            $this->addTranslations($record, $listingId);

            $this->publish($listingId, $record->variationBase->id);
        }
        else
        {
            $this->logger->log('Could not start listing for variation id: ' . $record->variationBase->id);
        }
    }

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

        return $this->listingService->createListing($this->config->get('EtsyIntegrationPlugin.shopLanguage'), $data); // TODO replace all languages with the shop language
    }

    private function addPictures(Record $record, int $listingId):void
    {
        $list = $this->itemHelper->getImageList($record->variationImageList['all_images']->toArray(), 'normal');

        foreach($list as $image)
        {
            $this->listingImageService->uploadListingImage($listingId, $image);
        }
    }

    private function addTranslations(Record $record, int $listingId):void
    {
        //TODO add foreach for the itemDescriptionList
        foreach($record->itemDescriptionList as $description)
        {
            if(
                in_array($description->lang, $this->config->get('EtsyIntegrationPlugin.exportLanguage'))
                && strlen($description->name1) > 0
                && strlen($description->description) > 0
            )
            {
                $this->listingTranslationService->createListingTranslation($listingId, $description, $description->lang);
            }
        }
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
