<?php

namespace Etsy\Helper;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\OrderType;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Models\Country;
use Plenty\Modules\Order\Shipping\Countries\Models\CountryState;
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
			55  => 67,
			57  => 52,
			95  => 69,
			250 => 70,
			228 => 71,
			56  => 72,
			251 => 73,
			252 => 75,
			59  => 76,
			60  => 51,
			253 => 77,
			61  => 29,
			62  => 2,
			63  => 78,
			229 => 79,
			232 => 80,
			68  => 81,
			237 => 82,
			71  => 48,
			65  => 3,
			72  => 83,
			66  => 84,
			225 => 85,
			76  => 86,
			73  => 87,
			70  => 88,
			77  => 89,
			254 => 90,
			74  => 39,
			255 => 91,
			231 => 247,
			75  => 92,
			69  => 44,
			67  => 93,
			64  => 94,
			135 => 95,
			84  => 96,
			79  => 30,
			222 => 97,
			247 => 98,
			78  => 99,
			196 => 100,
			81  => 101,
			82  => 31,
			257 => 102,
			258 => 103,
			86  => 104,
			259 => 105,
			85  => 106,
			260 => 108,
			87  => 109,
			118 => 54,
			338 => 258,
			89  => 5,
			90  => 6,
			93  => 7,
			92  => 113,
			261 => 114,
			94  => 115,
			96  => 116,
			97  => 53,
			187 => 117,
			111 => 118,
			98  => 119,
			100 => 9,
			101 => 120,
			262 => 121,
			241 => 122,
			234 => 123,
			102 => 11,
			103 => 10,
			115 => 124,
			263 => 125,
			264 => 126,
			104 => 127,
			109 => 128,
			106 => 129,
			91  => 1,
			107 => 130,
			226 => 131,
			112 => 13,
			113 => 132,
			245 => 133,
			265 => 61,
			266 => 134,
			114 => 135,
			108 => 137,
			110 => 138,
			116 => 139,
			119 => 140,
			267 => 141,
			268 => 142,
			117 => 143,
			219 => 57,
			120 => 14,
			126 => 144,
			122 => 38,
			121 => 145,
			125 => 147,
			123 => 16,
			269 => 148,
			127 => 59,
			128 => 15,
			83  => 37,
			129 => 149,
			131 => 32,
			130 => 151,
			132 => 47,
			133 => 152,
			270 => 153,
			137 => 156,
			134 => 46,
			138 => 158,
			146 => 18,
			139 => 159,
			143 => 160,
			140 => 161,
			141 => 162,
			272 => 34,
			144 => 33,
			145 => 17,
			273 => 163,
			151 => 164,
			149 => 165,
			158 => 166,
			159 => 56,
			238 => 55,
			152 => 168,
			227 => 19,
			274 => 169,
			275 => 170,
			157 => 171,
			239 => 172,
			276 => 173,
			150 => 36,
			277 => 174,
			148 => 175,
			278 => 35,
			154 => 176,
			155 => 177,
			279 => 178,
			147 => 50,
			156 => 179,
			153 => 180,
			160 => 181,
			280 => 182,
			166 => 183,
			243 => 184,
			233 => 185,
			167 => 66,
			163 => 186,
			161 => 187,
			162 => 188,
			281 => 189,
			282 => 190,
			283 => 191,
			165 => 20,
			168 => 192,
			169 => 193,
			284 => 194,
			285 => 195,
			170 => 196,
			173 => 197,
			178 => 198,
			171 => 199,
			172 => 200,
			174 => 23,
			177 => 22,
			175 => 202,
			179 => 203,
			304 => 204,
			180 => 41,
			181 => 40,
			182 => 205,
			286 => 206,
			287 => 207,
			244 => 208,
			289 => 209,
			249 => 210,
			290 => 211,
			291 => 212,
			292 => 213,
			183 => 214,
			185 => 215,
			189 => 216,
			293 => 217,
			186 => 218,
			220 => 25,
			337 => 259,
			191 => 26,
			192 => 27,
			242 => 219,
			188 => 220,
			215 => 221,
			294 => 222,
			136 => 155,
			99  => 8,
			142 => 223,
			190 => 225,
			295 => 226,
			194 => 227,
			193 => 24,
			80  => 4,
			204 => 60,
			199 => 229,
			205 => 230,
			198 => 62,
			164 => 21,
			296 => 231,
			197 => 232,
			297 => 233,
			298 => 234,
			201 => 235,
			202 => 236,
			203 => 63,
			200 => 237,
			299 => 238,
			300 => 239,
			206 => 240,
			207 => 241,
			58  => 254,
			105 => 12,
			209 => 28,
			302 => 242,
			208 => 243,
			248 => 248,
			210 => 49,
			221 => 244,
			211 => 245,
			212 => 246,
			224 => 249,
			213 => 250,
			214 => 58,
			216 => 107,
			217 => 252,
			218 => 253,
		];

		return $map[ $id ];
	}

	/**
	 * Get state ID by country ID and state ISO code.
	 *
	 * @param int    $countryId
	 * @param string $stateIsoCode
	 *
	 * @return int|null
	 */
	public function getStateIdByCountryIdAndIsoCode(int $countryId, string $stateIsoCode)
	{
		/** @var CountryRepositoryContract $countryRepo */
		$countryRepo = pluginApp(CountryRepositoryContract::class);

		if($countryRepo instanceof CountryRepositoryContract)
		{
			/** @var Country $country */
			$country = $countryRepo->getCountryById($countryId);

			/** @var CountryState $state */
			foreach($country->states as $state)
			{
				if($state->isoCode === $stateIsoCode)
				{
					return $state->id;
				}
			}
		}

		return null;
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
	public function isDirectCheckout($paymentMethod): bool
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
		$reExForeign = '/^(?<no>[0-9\s]{1,9}([\D]{0,2}([\s]|[^a-zA-Z0-9])))(?<ad>([\D]+))$/';    //e.g. "16 Bellevue Road"

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

		if(preg_match($reExForeign, $address, $matches) > 0)
		{
			$street      = trim($matches['ad']);
			$houseNumber = trim($matches['no']);
		}
		else if(preg_match($reEx, $address, $matches) > 0)
		{
			$street      = trim($matches['ad']);
			$houseNumber = trim($matches['no']);
		}
		else
		{
			$street      = $address;
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

		if($orderRepo instanceof OrderRepositoryContract)
		{
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
		}

		return false;
	}
}
