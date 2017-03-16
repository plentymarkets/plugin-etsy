<?php

namespace Etsy\Services\Country;

use Etsy\Api\Services\CountryService;
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Models\Country;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;

/**
 * Class CountryImportService
 *
 * Gets the countries from Etsy.
 */
class CountryImportService
{
	use Loggable;

	/**
	 * @var CountryRepositoryContract
	 */
	private $countryRepo;

	/**
	 * @var CountryService
	 */
	private $countryService;

	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @param ConfigRepository $config
	 * @param CountryService   $countryService
	 * @param CountryRepositoryContract $countryRepo
	 */
	public function __construct(ConfigRepository $config, CountryService $countryService, CountryRepositoryContract $countryRepo)
	{
		$this->config         = $config;
		$this->countryService = $countryService;
		$this->countryRepo    = $countryRepo;
	}

	/**
	 * Runs the order import process.
	 *
	 */
	public function run()
	{
		$countries = $this->countryService->findAllCountry();

		$countriesList = [];

		if(is_array($countries) && isset($countries['results']))
		{

			foreach($countries['results'] as $countryData)
			{
				$country = $this->countryRepo->getCountryByIso($countryData['iso_country_code'], 'iso_code_2');

				if($country instanceof Country)
				{
					$countriesList[$countryData['country_id']] = $country->id;
				}
			}
		}

		// TODO the list should be saved to dynamo db.
		
		return $countriesList;
	}
}
