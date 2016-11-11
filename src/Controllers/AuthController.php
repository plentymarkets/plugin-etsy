<?php

namespace Etsy\Controllers;

use Etsy\Api\Services\AuthService;
use Etsy\Helper\AccountHelper;
use Etsy\Models\Settings;
use Plenty\Modules\Helper\Services\WebstoreHelper;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class AuthController
 */
class AuthController extends Controller
{
	/**
	 * @var AuthService
	 */
	private $service;

	/**
	 * @var DataBase
	 */
	private $dataBase;

	/**
	 * @var AccountHelper
	 */
	private $accountHelper;

	/**
	 * @param AuthService $service
	 * @param DataBase    $dataBase
	 */
	public function __construct(AuthService $service, DataBase $dataBase, AccountHelper $accountHelper)
	{
		$this->service  = $service;
		$this->dataBase = $dataBase;
		$this->accountHelper = $accountHelper;
	}

	/**
	 * Check the authentication status.
	 *
	 * @return array
	 */
	public function status()
	{
		$tokenData = $this->accountHelper->getTokenData();

		$status = false;

		if( isset($tokenData['accessToken']) && strlen($tokenData['accessToken']) &&
			isset($tokenData['accessTokenSecret']) && strlen($tokenData['accessTokenSecret']))
		{
			$status = true;
		}

		return [
			'status' => $status
		];
	}

	/**
	 * Get the login url.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getLoginUrl()
	{
		/** @var WebstoreConfiguration $webstoreConfig */
		$webstore = pluginApp(WebstoreHelper::class)->getCurrentWebstoreConfiguration();

		$data = $this->service->getRequestToken($webstore->domainSsl . '/etsy/auth/access-token');

		if(isset($data['error']))
		{
			throw new \Exception($data['error']);
		}

		$this->saveTokenRequest($data);

		return [
			'loginUrl' => $data['login_url'],
		];
	}

	/**
	 * Exchange request token for access token.
	 *
	 * @param Request $request
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getAccessToken(Request $request)
	{
		try
		{
			$settings = $this->getTokenRequest();

			$requestTokenData = json_decode($settings->value, true);

			if(!$settings instanceof Settings)
			{
				throw new \Exception('Invalid token settings.');
			}

			$accessTokenData = $this->service->getAccessToken($requestTokenData['oauth_token'], $requestTokenData['oauth_token_secret'], $request->get('oauth_verifier'));

			$this->saveAccessToken($accessTokenData);

			return 'Login was successful. This window will close automatically.<script>window.close();</script>';
		}
		catch(\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}

	}

	/**
	 * Save the token request data.
	 *
	 * @param $data
	 */
	private function saveTokenRequest($data)
	{
		$settings = pluginApp(Settings::class);
		if($settings instanceof Settings)
		{
			$settings->id        = 1;
			$settings->name      = 'token_request';
			$settings->value     = (string) json_encode($data);
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
		$settings = pluginApp(Settings::class);

		if($settings instanceof Settings)
		{
			$settings->id        = 2;
			$settings->name      = 'access_token';
			$settings->value     = (string) json_encode($data);
			$settings->createdAt = date('Y-m-d H:i:s');
			$settings->updatedAt = date('Y-m-d H:i:s');

			$this->dataBase->save($settings);
		}
	}
}