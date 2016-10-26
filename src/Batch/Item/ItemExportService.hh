<?hh //strict

namespace Etsy\Batch\Item;

use Plenty\Plugin\Application;
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Models\Record;

use Etsy\Logger\Logger;
use Etsy\Batch\AbstractBatchService as Service;
use Etsy\Contracts\ItemDataProviderContract;
use Etsy\Factories\ItemDataProviderFactory;
use Etsy\Validators\StartListingValidator;
use Etsy\Services\Item\StartListingService;
use Etsy\Services\Item\VariationGrouper;

class ItemExportService extends Service
{
    private Application $app;

    private Logger $logger;

    private StartListingService $service;

	public function __construct(
        Application $app,
        Logger $logger,
        ItemDataProviderFactory $itemDataProviderFactory,
        StartListingService $service
    )
	{
        $this->app = $app;
        $this->logger = $logger;
        $this->service = $service;

		parent::__construct($itemDataProviderFactory->make('export'));
	}

    /**
     * Export all items.
     *
     * @param RecordList $records
     * @return void
     */
    protected function export(RecordList $records):void
    {
        foreach($records as $record)
		{
            try
            {
                StartListingValidator::validateOrFail([
                    // TODO fill here all data that we need for starting an etsy listing
                ]);

                $this->service->start($record);
            }
            catch(ValidationException $ex)
            {
                $messageBag = $ex->getMessageBag();

                if(!is_null($messageBag))
                {
                    $this->logger->log('Can not start listing: ...');
                }
            }
		}

    }
}
