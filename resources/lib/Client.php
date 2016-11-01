<?php

use Etsy\EtsyClient;
use Etsy\EtsyApi;
use Etsy\EtsyRequestException;

/**
 * Class Client
 */
class Client
{
	/**
	 * @var EtsyApi
	 */
    private $api;

	/**
	 * @var bool
	 */
    private $sandbox = false;

	/**
	 * @param string $consumerKey
	 * @param string $consumerSecret
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 * @param string $methodsJson
	 */
    public function __construct(
        $consumerKey,
        $consumerSecret,
        $accessToken,
        $accessTokenSecret,
        $methodsJson
    )
    {
        $client = new EtsyClient($consumerKey, $consumerSecret);
        $client->authorize($accessToken, $accessTokenSecret);

        $this->api = new EtsyApi($client, $methodsJson);
    }

	/**
	 * @param bool $sandbox
	 */
    public function sandbox($sandbox)
    {
        $this->sandbox = $sandbox;
    }

	/**
	 * Call a given api.
	 *
	 * @param string $method
	 * @param array $data
	 * @return array
	 */
    public function call($method, $data)
    {
        try
        {
            $data = $this->prepareData($method, $data);

            if(!$this->sandbox)
            {
                $response = $this->api->{$method}($data);
            }
            else
            {
                $response = json_decode(file_get_contents(__DIR__ . '/' . $method . '.json'));
            }

	        if(is_null($response))
	        {
		        throw new \Exception('No response.');
	        }

	        return $response;

        }
        catch(EtsyRequestException $ex)
        {
            return [
                'exception' => true,
                'message' => $ex->getLastResponse(),
            ];
        }

        catch(\Exception $ex)
        {
            return [
                'exception' => true,
                'message' => $ex->getMessage(),
            ];
        }
    }

	/**
	 * Prepare data for call.
	 *
	 * @param string $method
	 * @param array $data
	 * @return array
	 */
    private function prepareData($method, $data)
    {
        if($method == 'uploadListingImage')
        {
            $data = $this->prepareForImageUpload($data);
        }

        return $data;
    }

	/**
	 * Prepare data for image upload.
	 *
	 * @param array $data
	 * @return array
	 */
    private function prepareForImageUpload($data)
    {
        if(isset($data['data']) && isset($data['data']['image']))
        {
            $tempFile = tempnam(sys_get_temp_dir(), 'Etsy');

            file_put_contents($tempFile, file_get_contents($data['data']['image']));

            $data['data']['image'] = ['@' . $tempFile . ';type=' . mime_content_type($tempFile)];
        }

        return $data;
    }
}
