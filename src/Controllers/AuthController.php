<?php

namespace Etsy\Controllers;

use Etsy\Api\Services\AuthService;
use Etsy\Api\Services\TaxonomyService;
use Etsy\Contracts\TaxonomyRepositoryContract;
use Etsy\Helper\AccountHelper;
use Etsy\Helper\SettingsHelper;
use Etsy\Models\Taxonomy;
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
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @param AuthService   $service
     * @param AccountHelper $accountHelper
     */
    public function __construct(AuthService $service, AccountHelper $accountHelper, SettingsHelper $settingsHelper)
    {
        $this->service       = $service;
        $this->accountHelper = $accountHelper;
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * Check the authentication status.
     *
     * @return array
     */
    public function status()
    {
        $tokenData = $this->accountHelper->getTokenData();
        $shopData = json_decode($this->settingsHelper->get($this->settingsHelper::SETTINGS_ETSY_SHOPS), true);

        $shopId = key($shopData);

        $status = false;

        if (isset($tokenData['accessToken']) && strlen($tokenData['accessToken']) && isset($tokenData['accessTokenSecret']) && strlen($tokenData['accessTokenSecret']) && isset($tokenData['consumerKey']) && strlen($tokenData['consumerKey']) && isset($tokenData['consumerSecret']) && strlen($tokenData['consumerSecret'])) {
            $status = true;
        }

        if ($status) {
            return [
                [
                    'status' => $status,
                    'shopId' => $shopData[$shopId]['shopName'],
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

            $this->loadTaxonomies(); //todo: wenn möglich in neue Route auslagern

            return "<script>window.close()</script>";
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    protected function loadTaxonomies() {
        /** @var TaxonomyService $taxonomyService */
        $taxonomyService = pluginApp(TaxonomyService::class);
        /** @var TaxonomyRepositoryContract $taxonomyRepository */
        $taxonomyRepository = pluginApp(TaxonomyRepositoryContract::class);
        $taxonomies = $taxonomyService->getSellerTaxonomy('en');

        $savableArrays = [];

        foreach ($taxonomies as $data) {
            foreach ($this->createSavableArray($data) as $key => $item) {
                $savableArrays[$key] = $item;
            }
        }

        $taxonomies = $taxonomyService->getSellerTaxonomy('de');

        foreach ($taxonomies as $taxonomy) {
            foreach ($this->getTaxonomyChildren($taxonomy) as $child) {
                $savableArrays[$child['id']]['nameDe'] = $child['name'];
            }
        }

        $taxonomies = $taxonomyService->getSellerTaxonomy('fr');

        foreach ($taxonomies as $taxonomy) {
            foreach ($this->getTaxonomyChildren($taxonomy) as $child) {
                $savableArrays[$child['id']]['nameFr'] = $child['name'];
            }
        }

        foreach ($savableArrays as $savableArray) {
            /** @var Taxonomy $taxonomy */
            $taxonomy = pluginApp(Taxonomy::class);
            $taxonomy->fillByAttributes($savableArray);
            $taxonomyRepository->save($taxonomy);
        }
    }

    protected function createSavableArray($taxonomy)
    {
        $result = [];

        $result[$taxonomy['id']] = [
            'id' => $taxonomy['id'],
            'level' => $taxonomy['level'],
            'nameEn' => $taxonomy['name'],
            'parentId' => (isset($taxonomy['parent_id']) && !is_null($taxonomy['parent_id'])) ? $taxonomy['parent_id'] : 0,
            'isLeaf' => true,
            'path' => $taxonomy['path'],
            'children' => ''
        ];

        if (isset($taxonomy['children']) && count($taxonomy['children'])) {
            $result[$taxonomy['id']]['isLeaf'] = false;
            $children = [];

            foreach ($taxonomy['children'] as $child) {
               foreach ($this->createSavableArray($child) as $key => $item) {
                   $result[$key] = $item;
               }
                $children[] = $child['id'];
            }

            $result[$taxonomy['id']]['children'] = implode(',', $children);
        }

        return $result;
    }

    protected function getTaxonomyChildren($taxonomy) {
        $children = [$taxonomy['id'] => $taxonomy];

        if (isset($taxonomy['children']) && count($taxonomy['children'])) {

            foreach ($taxonomy['children'] as $child) {
                 foreach ($this->getTaxonomyChildren($child) as $key => $item) {
                     $children[$key] = $item;
                 }
            }
        }
        return $children;
    }
}