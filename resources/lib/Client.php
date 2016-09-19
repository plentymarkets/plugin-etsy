<?php

use Etsy\EtsyClient;
use Etsy\EtsyApi;
use Etsy\EtsyRequestException;

class Client
{
    private $api;

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

    public function call($method, $data)
    {
        try
        {
            $data = $this->prepareData($method, $data);

            return $this->api->{$method}($data);
        }
        catch(EtsyRequestException $ex)
        {
            return [
                'exception' => true,
                'message' => $ex->getLastResponse(),
            ];
        }

        catch(Exception $ex)
        {
            return [
                'exception' => true,
                'message' => $ex->getMessage(),
            ];
        }
    }

    private function prepareData($method, $data)
    {
        if($method == 'uploadListingImage')
        {
            $data = $this->prepareForImageUpload($data);
        }

        return $data;
    }

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
