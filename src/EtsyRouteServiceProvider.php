<?php

namespace Etsy;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\ApiRouter;
use Plenty\Plugin\Routing\Router as WebRouter;
use Etsy\Services\Order\OrderImportService;
use Etsy\Services\Taxonomy\TaxonomyImportService;

/**
 * Class EtsyRouteServiceProvider
 */
class EtsyRouteServiceProvider extends RouteServiceProvider
{
    /**
     * @param ApiRouter $api
     * @param WebRouter $webRouter
     */
    public function map(ApiRouter $api, WebRouter $webRouter)
    {
        /* TODO move this to jobs
        $router->get('etsy-test/order-import', ['middleware' => 'oauth', 'uses' => function(OrderImportService $service) {
            $service->run('2016-10-10 00:00:00', date('c'));
        }]);


        $router->get('etsy-test/taxonomies-import', ['middleware' => 'oauth', 'uses' => function(TaxonomyImportService $service) {
            $service->run('de');
            $service->run('en');
            $service->run('es');
            $service->run('fr');
            $service->run('it');
            $service->run('ja');
            $service->run('pt');
            $service->run('ru');
        }]);
        */


        $api->version(['v1'], ['middleware' => ['oauth']], function ($router) {

            /** Actions */
            $router->get('markets/etsy/actions/item-export', ['uses' => 'Etsy\Controllers\ActionController@itemExport']);
            $router->get('markets/etsy/actions/item-export/{ids}', ['uses' => 'Etsy\Controllers\ActionController@exportSpecificItems']);
            $router->get('markets/etsy/actions/stock-update', ['uses' => 'Etsy\Controllers\ActionController@stockUpdate']);
            $router->get('markets/etsy/actions/stock-update/{ids}', ['uses' => 'Etsy\Controllers\ActionController@updateItemStock']);
            $router->get('markets/etsy/actions/order-import', ['uses' => 'Etsy\Controllers\ActionController@orderImport']);

            /** Properties */
            $router->post('markets/etsy/properties/import', ['uses' => 'Etsy\Controllers\PropertyController@import']);
            $router->get('markets/etsy/properties', ['uses' => 'Etsy\Controllers\PropertyController@all']);
            $router->get('markets/etsy/properties/system-properties', ['uses' => 'Etsy\Controllers\PropertyController@systemProperties']);
            $router->post('markets/etsy/properties/correlations', ['uses' => 'Etsy\Controllers\PropertyController@saveCorrelations']);
            $router->get('markets/etsy/properties/correlations', ['uses' => 'Etsy\Controllers\PropertyController@getCorrelations']);

            /** Taxonomies */
            $router->get('markets/etsy/taxonomies', ['uses' => 'Etsy\Controllers\TaxonomyController@all']);
            $router->post('markets/etsy/taxonomies/correlations', ['uses' => 'Etsy\Controllers\TaxonomyController@saveCorrelations']);
            $router->get('markets/etsy/taxonomies/correlations', ['uses' => 'Etsy\Controllers\TaxonomyController@getCorrelations']);
            $router->get('markets/etsy/taxonomies/{id}', ['uses' => 'Etsy\Controllers\TaxonomyController@get']);

            /** Categories */
            $router->get('markets/etsy/categories', ['uses' => 'Etsy\Controllers\CategoryController@all']);
            $router->get('markets/etsy/categories/{id}', ['uses' => 'Etsy\Controllers\CategoryController@get']);

            /** Auth */
            $router->get('markets/etsy/auth/login-url', ['uses' => 'Etsy\Controllers\AuthController@getLoginUrl']);
            $router->get('markets/etsy/auth/status', ['uses' => 'Etsy\Controllers\AuthController@status']);
            $router->delete('markets/etsy/auth', ['uses' => 'Etsy\Controllers\AuthController@deleteAccount']);

            /** Settings */
            $router->post('markets/etsy/settings/save', ['uses' => 'Etsy\Controllers\SettingsController@save']);
            $router->get('markets/etsy/settings/all', ['uses' => 'Etsy\Controllers\SettingsController@all']);
            $router->get('markets/etsy/settings/shops', ['uses' => 'Etsy\Controllers\SettingsController@getShops']);
            
            /** Legal information */
            $router->get('markets/etsy/settings/legal-information', ['uses' => 'Etsy\Controllers\LegalInformationController@search']);
            $router->post('markets/etsy/settings/legal-information', ['uses' => 'Etsy\Controllers\LegalInformationController@save']);
            $router->get('markets/etsy/settings/legal-information/{id}', ['uses' => 'Etsy\Controllers\LegalInformationController@get']);
            $router->put('markets/etsy/settings/legal-information/{id}', ['uses' => 'Etsy\Controllers\LegalInformationController@update']);
            $router->delete('markets/etsy/settings/legal-information/{id}', ['uses' => 'Etsy\Controllers\LegalInformationController@delete']);

            /** Shipping Profiles */
            $router->get('markets/etsy/shipping-profiles/imported', ['uses' => 'Etsy\Controllers\ShippingProfileController@imported']);
            $router->post('markets/etsy/shipping-profiles/import', ['uses' => 'Etsy\Controllers\ShippingProfileController@import']);
            $router->get('markets/etsy/shipping-profiles/correlations', ['uses' => 'Etsy\Controllers\ShippingProfileController@correlations']);
            $router->get('markets/etsy/shipping-profiles/parcel-service-presets', ['uses' => 'Etsy\Controllers\ShippingProfileController@parcelServicePresets']);
            $router->post('markets/etsy/shipping-profiles/correlate', ['uses' => 'Etsy\Controllers\ShippingProfileController@correlate']);
        });

        $webRouter->get('markets/etsy/auth/access-token', ['uses' => 'Etsy\Controllers\AuthController@getAccessToken']);
    }
}
