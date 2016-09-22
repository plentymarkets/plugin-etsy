<?hh // strict

namespace Etsy\Services\Shipping;

use Plenty\Plugin\ConfigRepository;

use Etsy\Logger\Logger;
use Etsy\Api\Services\ShippingTemplateService;
use Etsy\Contracts\ShippingProfileRepositoryContract;

class ShippingProfileImportService
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
    * ShippingTemplateService $shippingTemplateService
    */
    private ShippingTemplateService $shippingTemplateService;

    /**
    * ShippintProfileRepositoryContract $shippingProfileRepository
    */
    private ShippingProfileRepositoryContract $shippingProfileRepository;

	public function __construct(
        Logger $logger,
        ConfigRepository $config,
        ShippingTemplateService $shippingTemplateService,
        ShippingProfileRepositoryContract $shippingProfileRepository
	)
	{
		$this->logger = $logger;
        $this->config = $config;
        $this->shippingTemplateService = $shippingTemplateService;
        $this->shippingProfileRepository = $shippingProfileRepository;
	}

	public function run():void
	{
		$shippingTemplateList = [27861830444];

		if(is_array($shippingTemplateList))
		{
			foreach($shippingTemplateList as $shippingTemplateId)
			{
			    $shippingTemplate = $this->shippingTemplateService->getShippingTemplate($shippingTemplateId, 'de');

                $this->shippingProfileRepository->create([
                    'id' => $shippingTemplate['shipping_template_id'],
                    'title' => $shippingTemplate['title'],
                    'minProcessingDays' =>  $shippingTemplate['min_processing_days'],
                    'maxProcessingDays' => $shippingTemplate['max_processing_days'],
                    'processingDaysDisplayLabel' => $shippingTemplate['processing_days_display_label'],
                    'originCountryId' => $shippingTemplate['origin_country_id'],
                ]);
			}
		}
	}
}
