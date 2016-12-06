<?php
namespace Etsy\Services\Item;

use Plenty\Plugin\ConfigRepository;
use Etsy\Api\Services\ListingService;
use Etsy\Logger\Logger;

/**
 * Class DeleteListingService
 */
class DeleteListingService
{
	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var ListingService
	 */
	private $listingService;


	/**
	 * @param Logger           $logger
	 * @param ListingService   $listingService
	 */
	public function __construct(
		Logger $logger,
		ListingService $listingService
	)
	{
		$this->logger = $logger;
		$this->listingService = $listingService;
	}

	/**
	 * @param mixed $listingId
	 * @return bool
	 */
	public function delete($listingId)
	{
		if(!is_null($listingId))
		{
			try
			{
				return $this->listingService->deleteListing($listingId);
			}
			catch(\Exception $e)
			{
				$this->logger->log('Could not delete listing for sku ' . $listingId . ': ' . $e->getMessage());
			}
		}
		else
		{
			$this->logger->log('Could not delete listing for sku ' . $listingId);
		}

		return false;
	}
}
