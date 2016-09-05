<?php

use Etsy\EtsyClient;
use Etsy\EtsyApi;

$consumerKey = SdkRestApi::getParam('consumerKey');
$consumerSecret = SdkRestApi::getParam('consumerSecret');
$accessToken = SdkRestApi::getParam('accessToken');
$accessTokenSecret = SdkRestApi::getParam('accessTokenSecret');

$method = SdkRestApi::getParam('method');
$params = SdkRestApi::getParam('params', []);
$data = SdkRestApi::getParam('data', []);
$fields = SdkRestApi::getParam('fields', []);
$associations = SdkRestApi::getParam('associations', []);

$client = new EtsyClient($consumerKey, $consumerSecret);
$client->authorize($accessToken, $accessTokenSecret);

$api = new EtsyApi($client);

return $api->{$method}([
	'params' => $params, 
	'data' => $data,
	'associations' => $associations, 
	'fields' => $fields,
]);