<?php
namespace Etsy\Services\Item;

use Plenty\Plugin\ConfigRepository;
use Etsy\Api\Services\ListingService;

/**
 * Class DeleteListingService
 */
class DeleteListingService
{
	/**
	 * @var ListingService
	 */
	private $listingService;


	/**
	 * @param ListingService $listingService
	 */
	public function __construct(ListingService $listingService)
	{
		$this->listingService = $listingService;
	}

	/**
	 * Delete a listing.
	 *
	 * @param mixed $listingId
	 *
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
				// $this->logger->log('Could not delete listing ID ' . $listingId . ': ' . $e->getMessage());
			}
		}
		else
		{
			// $this->logger->log('Could not delete listing ID ' . $listingId);
		}

		return false;
	}
}
