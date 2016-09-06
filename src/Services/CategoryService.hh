<?hh //strict

namespace Etsy\Services;

class Category
{
    const API_KEY       = 'pb3qb621tm9boiau3nm7m25l';
    const URL			= 'https://openapi.etsy.com/v2/';
    const POST			= 'POST';
    const PUT			= 'PUT';
    const GET			= 'GET';

    /**
     * @return string
     */
    public function getCategory():string
    {
        $url = self::URL.'taxonomy/buyer/get?api_key='.self::API_KEY;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }
}