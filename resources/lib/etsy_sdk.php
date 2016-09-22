<?php
require_once __DIR__ . '/Client.php';

$client = new Client(
    SdkRestApi::getParam('consumerKey'),
    SdkRestApi::getParam('consumerSecret'),
    SdkRestApi::getParam('accessToken'),
    SdkRestApi::getParam('accessTokenSecret'),
    __DIR__ . '/methods.json'
);

if(SdkRestApi::getParam('sandbox', false))
{
    $client->sandbox(true);
}

return $client->call(
    SdkRestApi::getParam('method'),
    [
        'params'        => SdkRestApi::getParam('params', []),
    	'data'          => SdkRestApi::getParam('data', []),
    	'associations'  => SdkRestApi::getParam('associations', []),
    	'fields'        => SdkRestApi::getParam('fields', []),
    ]
);
