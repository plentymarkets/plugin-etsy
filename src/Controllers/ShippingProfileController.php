<?php

namespace Etsy\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Etsy\Contracts\ShippingProfileRepositoryContract;
use Etsy\Services\Shipping\ShippingProfileImportService;

/**
 * Class ShippingProfileController
 */
class ShippingProfileController extends Controller
{
	/**
	 * @var ShippingProfileRepositoryContract
	 */
	private $shippingProfileRepository;

	/**
	 * @var ShippingProfileImportService
	 */
	private $service;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param ShippingProfileRepositoryContract $shippingProfileRepository
	 * @param Request                           $request
	 * @param ShippingProfileImportService      $service
	 */
	public function __construct(ShippingProfileRepositoryContract $shippingProfileRepository, Request $request, ShippingProfileImportService $service)
	{
		$this->shippingProfileRepository = $shippingProfileRepository;
		$this->request                   = $request;
		$this->service                   = $service;
	}

	/**
	 * @return void
	 */
	public function importShippingProfiles()
	{
		$this->service->run();
	}
}
