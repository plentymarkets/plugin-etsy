<?php

namespace Etsy\Controllers;

use Etsy\Api\Services\AuthService;
use Etsy\Api\Services\ShopService;
use Etsy\Helper\AccountHelper;
use Etsy\Helper\SettingsHelper;
use Etsy\Services\Shop\ShopImportService;
use Plenty\Modules\Helper\Services\WebstoreHelper;
use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

/**
 * Class AuthController
 */
class AuthController extends Controller
{
    use Loggable;

    /**
     * @var AuthService
     */
    private $service;

    /**
     * @var AccountHelper
     */
    private $accountHelper;

    /**
     * @param AuthService   $service
     * @param AccountHelper $accountHelper
     */
    public function __construct(AuthService $service, AccountHelper $accountHelper)
    {
        $this->service       = $service;
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

        /** @var ConfigRepository $configRepo */
        $configRepo = pluginApp(ConfigRepository::class);

        $status = false;

        if (isset($tokenData['accessToken']) && strlen($tokenData['accessToken']) && isset($tokenData['accessTokenSecret']) && strlen($tokenData['accessTokenSecret']) && isset($tokenData['consumerKey']) && strlen($tokenData['consumerKey']) && ($configRepo->get('Etsy.consumerKey', '') == $tokenData['consumerKey']) && isset($tokenData['consumerSecret']) && strlen($tokenData['consumerSecret']) && ($configRepo->get('Etsy.consumerSecret', '') == $tokenData['consumerSecret'])) {
            $status = true;
        }

        if ($status) {
            return [
                [
                    'status'            => $status,
                    'consumerKey'       => $tokenData['consumerKey'],
                    'sharedSecret'      => $tokenData['consumerSecret'],
                ]
            ];
        }

        return [];
    }

    /**
     * Delete account and all its settings.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function deleteAccount(Request $request, Response $response)
    {
        /** @var DynamoDbRepositoryContract $dynamoDbRepo */
        $dynamoDbRepo = pluginApp(DynamoDbRepositoryContract::class);

        $dynamoDbRepo->deleteItem(SettingsHelper::PLUGIN_NAME, SettingsHelper::TABLE_NAME, ['name' => SettingsHelper::SETTINGS_TOKEN_REQUEST]);

        $dynamoDbRepo->deleteItem(SettingsHelper::PLUGIN_NAME, SettingsHelper::TABLE_NAME, ['name' => SettingsHelper::SETTINGS_ACCESS_TOKEN]);

        $dynamoDbRepo->deleteItem(SettingsHelper::PLUGIN_NAME, SettingsHelper::TABLE_NAME, ['name' => SettingsHelper::SETTINGS_ETSY_SHOPS]);

        $dynamoDbRepo->deleteItem(SettingsHelper::PLUGIN_NAME, SettingsHelper::TABLE_NAME, ['name' => SettingsHelper::SETTINGS_SETTINGS]);

        return $response->make('', 201);
    }

    /**
     * Get the login url.
     *
     * @param WebstoreHelper $webstoreHelper
     *
     * @return array
     * @throws \Exception
     */
    public function getLoginUrl(WebstoreHelper $webstoreHelper)
    {
        /** @var WebstoreConfiguration $webstoreConfig */
        $webstore = $webstoreHelper->getCurrentWebstoreConfiguration();

        try {
            $data = $this->service->getRequestToken($webstore->domainSsl . '/markets/etsy/auth/access-token');
        } catch (\Exception $ex) {
            $this->getLogger(__FUNCTION__)
                 ->error('Etsy::authentication.authenticateError', $ex->getMessage());

            $data = $this->service->getRequestToken($webstore->domainSsl . '/markets/etsy/auth/access-token');
        }

        if (isset($data['error'])) {
            throw new \Exception($data['error_msg']);
        }

        $this->accountHelper->saveTokenRequest($data);

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
        try {
            $tokenRequestData = $this->accountHelper->getTokenRequest();

            $accessTokenData = $this->service->getAccessToken($tokenRequestData['oauth_token'], $tokenRequestData['oauth_token_secret'], $request->get('oauth_verifier'));

            $this->accountHelper->saveAccessToken($accessTokenData);

            pluginApp(ShopImportService::class)->run();

            return 'Login was successful. This window will close automatically.<script>window.close();</script>';
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}