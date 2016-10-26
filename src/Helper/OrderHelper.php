<?php

namespace Etsy\Helper;

/**
 * Class OrderHelper
 */
class OrderHelper
{
	/**
	 * @param string $address
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
	 * @return int
	 */
	public function getPaymentMethodId($paymentMethod)
	{
		$map = [
			'other' => 0,
			'pp'    => 14,
			'cc'    => 12,
			'ck'    => 1, // TODO not sure
			'mo'    => 1, // TODO not sure
		];

		return $map[ $paymentMethod ];
	}

	/**
	 * @param string $address
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
}
