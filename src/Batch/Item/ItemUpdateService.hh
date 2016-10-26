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
use Etsy\Validators\UpdateListingValidator;
use Etsy\Services\Item\UpdateListingService;

class ItemUpdateService extends Service
{
    private Application $app;

    private Logger $logger;

    private UpdateListingService $service;

    public function __construct(
        Application $app,
        Logger $logger,
        ItemDataProviderFactory $itemDataProviderFactory,
        UpdateListingService $service
    )
	{
        $this->app = $app;
        $this->logger = $logger;
        $this->service = $service;

		parent::__construct($itemDataProviderFactory->make('update'));
	}

    /**
     * Update all article which are not updated yet.
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
                UpdateListingValidator::validateOrFail([
                    // TODO fill here all data that we need for starting an etsy listing
                ]);

                $this->service->update($record);
            }
            catch (ValidationException $ex)
            {
                $messageBag = $ex->getMessageBag();

                if(!is_null($messageBag))
                {
                    $this->logger->log('Can not update Stock: ...');
                }
            }
        }
    }
}
