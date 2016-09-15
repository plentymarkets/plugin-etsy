<?hh //strict

namespace Etsy\Helper;

class OrderHelper
{
    public function getStreetName(string $address):string
    {
        $extracted = $this->extractAddress($address);

        if(strlen($extracted['street']))
		{
			return $extracted['street'];
		}

        return '';
    }

    public function getHouseNumber(string $address):string
    {
        $extracted = $this->extractAddress($address);

        if(strlen($extracted['houseNumber']))
		{
			return $extracted['houseNumber'];
		}

        return '';
    }

    public function getCountryIdByEtsyCountryId(int $id):int
    {
        $map = [
            91 => 1, // Germany
            62 => 2, // Austria
            80 => 4, // Switzerland
            103 => 10, // France
            105 => 12, // UK
            209 => 28, // USA
            128 => 15, // Italy
            164 => 21, // Netherlands
            65 => 3, // Belgium
            99 => 8, // Spain
        ];

        return $map[$id];
    }

    public function getPaymentMethodId(string $paymentMethod):int
    {
        $map = [
            'other' => 0,
            'pp' => 14,
            'cc' => 12,
            'ck' => 1, // TODO not sure
            'mo' => 1, // TODO not sure
        ];

        return $map[$paymentMethod];
    }

    private function extractAddress(string $address):array<string,string>
    {
        $address = trim($address);

        $matches = [];

		if(preg_match("/(^.*?)([0-9]{1,}$|[0-9]{1,}[a-z]?.*?$)/i", $address, $matches) != 1)
		{
			$matches = [
                1 => $address
            ];
		}

		return [
            'street' => $matches[1],
            'houseNumber' => $matches[2],
        ];
    }
}
