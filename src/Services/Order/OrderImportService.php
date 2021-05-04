<?php

namespace Etsy\Services\Order;

use Etsy\Helper\OrderHelper;
use Etsy\Helper\SettingsHelper;
use Etsy\Services\Country\CountryImportService;
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Etsy\Api\Services\ReceiptService;
use Etsy\Validators\EtsyReceiptValidator;
use Plenty\Plugin\Log\Loggable;

/**
 * Class OrderImportService
 * Gets the orders from Etsy and imports them into plentymarkets.
 */
class OrderImportService
{
	use Loggable;

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
	 * @var CountryImportService
	 */
	private $countryImportService;

	/**
	 * @var CountryRepositoryContract
	 */
	private $countryRepositoryContract;

	/**
	 * @param OrderCreateService $orderCreateService
	 * @param ConfigRepository   $config
	 * @param ReceiptService     $receiptService
	 * @param SettingsHelper     $settingsHelper
	 * @param OrderHelper        $orderHelper
	 * @param CountryImportService $countryImportService
	 * @param CountryRepositoryContract $countryRepositoryContract
	 */
	public function __construct(OrderCreateService $orderCreateService, ConfigRepository $config, ReceiptService $receiptService, SettingsHelper $settingsHelper, OrderHelper $orderHelper, CountryImportService $countryImportService, CountryRepositoryContract $countryRepositoryContract)
	{
		$this->orderCreateService = $orderCreateService;
		$this->config             = $config;
		$this->receiptService     = $receiptService;
		$this->settingsHelper     = $settingsHelper;
		$this->orderHelper        = $orderHelper;
		$this->countryImportService = $countryImportService;
		$this->countryRepositoryContract = $countryRepositoryContract;
	}

	/**
	 * Runs the order import process.
	 *
	 * @param string $from
	 * @param string $to
	 *
	 * @throws \Exception
	 */
	public function run($from, $to)
	{
		$lang = $this->settingsHelper->getShopSettings('mainLanguage', 'de');

		$shopId = $this->settingsHelper->getShopSettings('shopId');

		$receipts = $this->receiptService->findAllShopReceipts($shopId,$lang, $from, $to);

		$countries = $this->countryImportService->run();

		if(isset($receipts['error']) && $receipts['error'] === true)
		{
			throw new \Exception($receipts['error_msg']);
		}
		elseif(isset($receipts['results'])) {
			foreach($receipts['results'] as $receiptData) {
				try {
					EtsyReceiptValidator::validateOrFail($receiptData);

                    /** @var OrderRepositoryContract $orderRepo */
                    $orderRepo = pluginApp(OrderRepositoryContract::class);

                    try {
                        $order = $orderRepo->findOrderByExternalOrderId($shopId . '_' . $receiptData['receipt_id']);
                    } catch (\Exception $exception) {

                    }


                    if (isset($order) &&
                        $this->orderHelper->isDirectCheckout((string)$receiptData['payment_method'])) {
                        if ($order->paymentStatus == 'unpaid' &&
                            $receiptData['was_paid'] == true
                        ) {
                            $this->orderHelper->createPayment($receiptData, $order);
                            //TODO: Log for added payment
                        } elseif ($order->paymentStatus == 'unpaid' &&
                            $receiptData['was_paid'] == false
                        ) {
                            continue;
                        } else {
                            $this->getLogger(__FUNCTION__)
                                ->addReference('etsyReceiptId', $receiptData['receipt_id'])
                                ->info('Etsy::order.orderAlreadyImported');
                        }
                    } else {
						$this->getLogger(__FUNCTION__)
							->addReference('etsyReceiptId', $receiptData['receipt_id'])
							->report('Etsy::order.startOrderImport', $receiptData);

						if (array_key_exists($receiptData['country_id'], $countries)){
							$countryId = $countries[$receiptData['country_id']];
							$country = $this->countryRepositoryContract->getCountryById($countryId);
							$countryLang = $country->lang;

							$this->orderCreateService->create($receiptData, $countryLang);
						} else {
							$this->orderCreateService->create($receiptData);
						}
					}
				}
				catch(ValidationException $ex)
				{
					$messageBag = $ex->getMessageBag();

					if(!is_null($messageBag))
					{
						$this->getLogger(__FUNCTION__)
						     ->addReference('etsyReceiptId', $receiptData['receipt_id'])
						     ->error('Etsy::order.orderImportError', $messageBag);
					}
				}
				catch(\Exception $ex)
				{
					$this->getLogger(__FUNCTION__)
							->addReference('etsyReceiptId', $receiptData['receipt_id'])
					        ->error('Etsy::order.orderImportError', $ex->getMessage());
				}
			}
		}
	}
}