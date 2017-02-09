<?php

namespace Etsy\Helper;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\OrderType;
use Plenty\Repositories\Models\PaginatedResult;

/**
 * Class OrderHelper
 */
class OrderHelper
{
	/**
	 * @var PaymentHelper
	 */
	private $paymentHelper;

	/**
	 * @var SettingsHelper
	 */
	private $settingsHelper;

	/**
	 * @param PaymentHelper  $paymentHelper
	 * @param SettingsHelper $settingsHelper
	 */
	public function __construct(PaymentHelper $paymentHelper, SettingsHelper $settingsHelper)
	{
		$this->paymentHelper  = $paymentHelper;
		$this->settingsHelper = $settingsHelper;
	}

	/**
	 * Get the registered referrer ID.
	 *
	 * @return null|string
	 */
	public function getReferrerId()
	{
		return $this->settingsHelper->get(SettingsHelper::SETTINGS_ORDER_REFERRER);
	}

	/**
	 * @param string $address
	 *
	 * @return string
	 */
	public function getStreetName($address)
	{
		$extracted = $this->extractAddress($address);

		if(strlen($extracted['street']))
		{
			return $extracted['street'];
		}

		return '';
	}

	/**
	 * @param string $address
	 *
	 * @return string
	 */
	public function getHouseNumber($address)
	{
		$extracted = $this->extractAddress($address);

		if(strlen($extracted['houseNumber']))
		{
			return $extracted['houseNumber'];
		}

		return '';
	}

	/**
	 * @param int $id
	 *
	 * @return int
	 */
	public function getCountryIdByEtsyCountryId($id)
	{
		$map = [
			91  => 1, // Germany
			62  => 2, // Austria
			80  => 4, // Switzerland
			103 => 10, // France
			105 => 12, // UK
			209 => 28, // USA
			128 => 15, // Italy
			164 => 21, // Netherlands
			65  => 3, // Belgium
			99  => 8, // Spain
		];

		return $map[ $id ];
	}

	/**
	 * @param string $paymentMethod
	 *
	 * @return int
	 */
	public function getPaymentMethodId($paymentMethod)
	{
		$map = [
			'other' => 0,
			'pp'    => 14,
			'cc'    => $this->paymentHelper->getPaymentMethodId(), // etsy direct checkout,
			'ck'    => 1, // TODO not sure
			'mo'    => 1, // TODO not sure
		];

		return $map[ $paymentMethod ];
	}

	/**
	 * Check if payment method is Etsy direct checkout.
	 *
	 * @param string $paymentMethod
	 *
	 * @return bool
	 */
	public function isDirectCheckout($paymentMethod):bool
	{
		return $this->getPaymentMethodId($paymentMethod) == $this->paymentHelper->getPaymentMethodId();
	}

	/**
	 * Extract house number and street from address line.
	 *
	 * @param string $address
	 *
	 * @return array
	 */
	private function extractAddress($address)
	{
		$address = trim($address);

		$reEx = '/(?<ad>(.*?)[\D]{3}[\s,.])(?<no>';
		$reEx .= '|[0-9]{1,3}[ a-zA-Z-\/\.]{0,6}'; // e.g. "Rosenstr. 14"
		$reEx .= '|[0-9]{1,3}[ a-zA-Z-\/\.]{1,6}[0-9]{1,3}[ a-zA-Z-\/\.]{0,6}[0-9]{0,3}[ a-zA-Z-\/\.]{0,6}[0-9]{0,3}'; // e.g "Straße in Österreich 30/4/12.2"
		$reEx .= ')$/';
		$reExForeign = '/^(?<no>[0-9]{1,4}([\D]{0,2}([\s]|[^a-zA-Z0-9])))(?<ad>([\D]+))$/';    //e.g. "16 Bellevue Road"

		/*
		if (strripos($address, 'POSTFILIALE') !== false)
		{
			if (preg_match("/([\D].*?)(([\d]{4,})|(?<id>[\d]{3}))([\D]*?)/i", $address, $matches) > 0)
			{
				$id = $matches['id'];

				$address = preg_replace("/([\D].*?)" . $matches['id'] . "([\D]*)/i", '\1\2', $address);

				if ($id && preg_match("/(?<id>[\d\s]{6,14})/i", $address, $matches) > 0
				)
				{
					$street = preg_replace("/\s/", '', $matches['id']) . ' ' . 'POSTFILIALE';
					$houseNumber = $id;

					return array(
						'street'      => $street,
						'houseNumber' => $houseNumber,
					);
				}
			}
		}
		*/

		if (preg_match($reExForeign, $address, $matches) > 0)
		{
			$street = trim($matches['ad']);
			$houseNumber = trim($matches['no']);
		}
		else if (preg_match($reEx, $address, $matches) > 0)
		{
			$street = trim($matches['ad']);
			$houseNumber = trim($matches['no']);
		}
		else
		{
			$street = $address;
			$houseNumber = '';
		}

		return array(
			'street'      => $street,
			'houseNumber' => $houseNumber,
		);
	}

	/**
	 * Extract the first and last name.
	 *
	 * @param string $name
	 *
	 * @return array
	 */
	public function extractName($name)
	{
		$name = trim($name);

		$pos = strrpos($name, ' ');

		if($pos > 0)
		{
			$lastName  = trim(substr($name, $pos));
			$firstName = trim(substr($name, 0, - strlen($lastName)));
		}
		else
		{
			// no space character was found, don't split
			$lastName  = $name;
			$firstName = '';
		}

		return array(
			'firstName' => $firstName,
			'lastName'  => $lastName,
		);
	}

	/**
	 * Check if order was already imported.
	 *
	 * @param mixed $externalOrderId
	 *
	 * @return bool
	 */
	public function orderWasImported($externalOrderId)
	{
		/** @var OrderRepositoryContract $orderRepo */
		$orderRepo = pluginApp(OrderRepositoryContract::class);

		// if($orderRepo instanceof OrderRepositoryContract) TODO uncomment this as soon as setFilters is available
		// {
			$orderRepo->setFilters([
				                       'externalOrderId' => $externalOrderId,
				                       'referrerId'      => $this->getReferrerId(),
				                       'orderType'       => OrderType::TYPE_SALES_ORDER,
			                       ]);

			/** @var PaginatedResult $paginatedResult */
			$paginatedResult = $orderRepo->searchOrders();

			if($paginatedResult instanceof PaginatedResult)
			{
				if($paginatedResult->getTotalCount() > 0)
				{
					return true;
				}
			}
		// }

		return false;
	}
}
