<?php

namespace Etsy\Services\Order;

use Etsy\Helper\OrderHelper;
use Etsy\Helper\SettingsHelper;
use Plenty\Exceptions\ValidationException;
use Plenty\Plugin\ConfigRepository;

use Etsy\Api\Services\ReceiptService;
use Etsy\Logger\Logger;
use Etsy\Services\Order\OrderCreateService;
use Etsy\Validators\EtsyReceiptValidator;

/**
 * Class OrderImportService
 * Gets the orders from Etsy and imports them into plentymarkets.
 */
class OrderImportService
{
	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @var OrderCreateService
	 */
	private $orderCreateService;

	/**
	 * @var ReceiptService
	 */
	private $receiptService;

	/**
	 * @var SettingsHelper
	 */
	private $settingsHelper;

	/**
	 * @var OrderHelper
	 */
	private $orderHelper;

	/**
	 * @param Logger             $logger
	 * @param OrderCreateService $orderCreateService
	 * @param ConfigRepository   $config
	 * @param ReceiptService     $receiptService
	 * @param SettingsHelper     $settingsHelper
	 * @param OrderHelper        $orderHelper
	 */
	public function __construct(
		Logger $logger,
		OrderCreateService $orderCreateService,
		ConfigRepository $config,
		ReceiptService $receiptService,
		SettingsHelper $settingsHelper,
		OrderHelper $orderHelper
	)
	{
		$this->logger             = $logger;
		$this->orderCreateService = $orderCreateService;
		$this->config             = $config;
		$this->receiptService     = $receiptService;
		$this->settingsHelper     = $settingsHelper;
		$this->orderHelper        = $orderHelper;
	}

	/**
	 * Runs the order import process.
	 *
	 * @param string $from
	 * @param string $to
	 */
	public function run($from, $to)
	{
		$receipts = $this->receiptService->findAllShopReceipts($this->settingsHelper->getShopSettings('shopId'), $from, $to);

		if(is_array($receipts))
		{
			foreach($receipts as $receiptData)
			{
				try
				{
					EtsyReceiptValidator::validateOrFail($receiptData);

					if($this->orderHelper->orderWasImported($receiptData['receipt_id']))
					{
						throw new \Exception('Order was already imported.');
					}

					$this->orderCreateService->create($receiptData);
				}
				catch(ValidationException $ex)
				{
					$messageBag = $ex->getMessageBag();

					if(!is_null($messageBag))
					{
						$this->logger->log('Can not import order.'); // TODO show the message bag
					}
				}
				catch(\Exception $ex)
				{
					$this->logger->log('Can not import order: ' . $ex->getMessage());
				}
			}
		}
	}
}
