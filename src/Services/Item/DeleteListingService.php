<?php
namespace Etsy\Services\Item;

use Plenty\Plugin\ConfigRepository;
use Etsy\Api\Services\ListingService;
use Plenty\Plugin\Log\Loggable;

/**
 * Class DeleteListingService
 */
class DeleteListingService
{
	use Loggable;

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
			catch(\Exception $ex)
			{
				$this->getLogger(__FUNCTION__)
					->setReferenceType('listingId')
					->setReferenceValue($listingId)
					->error('Etsy::item.deleteListingError', $ex);
			}
		}
		else
		{
			$this->getLogger(__FUNCTION__)
				->setReferenceType('listingId')
				->setReferenceValue($listingId)
				->info('Etsy::item.deleteListingError');
		}

		return false;
	}
}
