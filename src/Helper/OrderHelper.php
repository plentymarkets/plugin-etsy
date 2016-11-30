<?php

namespace Etsy\Helper;

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
	 * @param string $address
	 *
	 * @return array
	 */
	private function extractAddress($address)
	{
		$address = trim($address);

		$matches = [];

		if(preg_match("/(^.*)([0-9]{1,}$|[0-9]{1,}[a-z]?.*?$)/i", $address, $matches) != 1)
		{
			$matches = [
				1 => $address
			];
		}

		return [
			'street'      => $matches[1],
			'houseNumber' => $matches[2],
		];
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
}
