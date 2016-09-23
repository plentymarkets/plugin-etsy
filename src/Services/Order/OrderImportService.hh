<?hh // strict

namespace Etsy\Services\Order;

use Plenty\Exceptions\ValidationException;
use Plenty\Plugin\ConfigRepository;

use Etsy\Api\Services\ReceiptService;
use Etsy\Logger\Logger;
use Etsy\Services\Order\OrderCreateService;
use Etsy\Validators\EtsyReceiptValidator;

/**
 * Class OrderImportService
 *
 * Gets the orders from Etsy and imports them into plentymarkets.
 *
 * @package Etsy\Service
 */
class OrderImportService
{
	/**
	 * Logger $logger
	 */
	private Logger $logger;

    /**
    * ConfigRepository $config
    */
    private ConfigRepository $config;

    /**
    * OrderCreateService $orderCreateService
    */
    private OrderCreateService $orderCreateService;

    /**
    * ReceiptService $receiptService
    */
    private ReceiptService $receiptService;

	/**
	 * @param Client $client
	 * @param ConfigRepository $config
	 */
	public function __construct(
        Logger $logger,
        OrderCreateService $orderCreateService,
        ConfigRepository $config,
        ReceiptService $receiptService
	)
	{
		$this->logger = $logger;
        $this->orderCreateService = $orderCreateService;
        $this->config = $config;
        $this->receiptService = $receiptService;
	}

	/**
	 * Runs the order import process.
	 *
	 * @param string $from
	 * @param string $to
	 */
	public function run(string $from, string $to):void
	{
		$receipts = $this->receiptService->findAllShopReceipts($this->config->get('EtsyIntegrationPlugin.shopId'), $from, $to);

		if(is_array($receipts))
		{
			foreach($receipts as $receiptData)
			{
				try
				{
					EtsyReceiptValidator::validateOrFail($receiptData);

                    $this->orderCreateService->create($receiptData);
				}
				catch(ValidationException $ex)
				{
					$messageBag = $ex->getMessageBag();

                    if(!is_null($messageBag))
                    {
                        $this->logger->log('Can not import order: ...');
                    }
				}
			}
		}
	}
}
