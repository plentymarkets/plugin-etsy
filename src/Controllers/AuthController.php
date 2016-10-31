<?php

namespace Etsy\Controllers;

use Etsy\Api\Services\AuthService;
use Etsy\Models\Settings;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Plugin\Application;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

/**
 * Class AuthController
 */
class AuthController extends Controller
{
	private $service;

	private $app;

	private $dataBase;


	public function __construct(AuthService $service, Application $app, DataBase $dataBase)
	{
		$this->service = $service;
		$this->app = $app;
		$this->dataBase = $dataBase;

	}

	public function showLogin()
	{
		$data = $this->service->getRequestToken('http://master.plentymarkets.com/etsy/auth-token');

		if(isset($data['error']))
		{
			return 'Error!!'; // TODO handle errors
		}

		$this->saveTokenRequest($data);

		return '<a href="' . $data['login_url'] . '">Login to Etsy</a>';
	}

	public function getToken(Request $request)
	{
		$settings = $this->getTokenRequest();

		$requestTokenData = json_decode($settings->value, true);

		if($settings instanceof Settings)
		{
			$accessTokenData = $this->service->getAccessToken($requestTokenData['oauth_token'], $requestTokenData['oauth_token_secret'], $request->get('oauth_verifier'));

			$this->saveAccessToken($accessTokenData);

			return 'Done.';
		}
		else
		{
			return 'Error!! Not settings found'; // TODO handle errors
		}
	}

	/**
	 * Save the token request data.
	 *
	 * @param $data
	 */
	private function saveTokenRequest($data)
	{
		$settings = $this->app->make(Settings::class);
		if($settings instanceof Settings)
		{
			$settings->id = 1;
			$settings->name = 'token_request';
			$settings->value = (string) json_encode($data);
			$settings->createdAt = date('Y-m-d H:i:s');
			$settings->updatedAt = date('Y-m-d H:i:s');

			$this->dataBase->save($settings);
		}
	}

	/**
	 * Get the token request data.
	 *
	 * @return null|Settings
	 */
	private function getTokenRequest()
	{
		return $this->dataBase->find(Settings::class, 1);
	}

	/**
	 * Save the access token data.
	 *
	 * @param array $data
	 */
	private function saveAccessToken($data)
	{
		$settings = $this->app->make(Settings::class);
		if($settings instanceof Settings)
		{
			$settings->id = 2;
			$settings->name = 'access_token';
			$settings->value = (string) json_encode($data);
			$settings->createdAt = date('Y-m-d H:i:s');
			$settings->updatedAt = date('Y-m-d H:i:s');

			$this->dataBase->save($settings);
		}
	}
}