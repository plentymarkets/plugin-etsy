<?php
require_once __DIR__ . '/Client.php';
require_once __DIR__ . '/AuthClient.php';

$method         = SdkRestApi::getParam('method');
$consumerKey    = SdkRestApi::getParam('consumerKey');
$consumerSecret = SdkRestApi::getParam('consumerSecret');

if(in_array($method, ['getRequestToken', 'getAccessToken']))
{
	$client = new AuthClient(
		$consumerKey,
		$consumerSecret
	);

	return $client->{$method}(SdkRestApi::getParam('params', []));
}
else
{
	$client = new Client(
		$consumerKey,
		$consumerSecret,
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

}

