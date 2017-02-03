<?php

namespace Etsy;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Etsy\Services\Order\OrderImportService;
use Etsy\Services\Taxonomy\TaxonomyImportService;

/**
 * Class EtsyRouteServiceProvider
 */
class EtsyRouteServiceProvider extends RouteServiceProvider
{
	/**
	 * @param Router $router
	 */
	public function map(Router $router)
	{
		/*
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

		$router->get('markets/etsy/actions/item-export', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\ActionController@itemExport'
		]);

		$router->get('markets/etsy/actions/stock-update', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\ActionController@stockUpdate'
		]);

		$router->get('markets/etsy/actions/order-import', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\ActionController@orderImport'
		]);

		/** Properties */
		$router->post('markets/etsy/properties/import', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\PropertyController@import'
		]);

		$router->get('markets/etsy/properties/imported', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\PropertyController@imported'
		]);

		$router->get('markets/etsy/properties/properties', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\PropertyController@properties'
		]);

		$router->post('markets/etsy/properties/correlate', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\PropertyController@correlate'
		]);

		$router->get('markets/etsy/properties/correlations', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\PropertyController@correlations'
		]);

		/** Taxonomies */
		$router->get('markets/etsy/taxonomies/imported', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\TaxonomyController@imported'
		]);

		$router->get('markets/etsy/taxonomies/categories', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\TaxonomyController@categories'
		]);

		$router->post('markets/etsy/taxonomies/correlate', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\TaxonomyController@correlate'
		]);

		$router->get('markets/etsy/taxonomies/correlations', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\TaxonomyController@correlations'
		]);

		/** Auth */
		$router->get('markets/etsy/auth/login-url', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\AuthController@getLoginUrl'
		]);

		$router->get('markets/etsy/auth/access-token', [
			'uses'       => 'Etsy\Controllers\AuthController@getAccessToken'
		]);

		$router->get('markets/etsy/auth/status', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\AuthController@status'
		]);

		/** Settings */
		$router->post('markets/etsy/settings/save', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\SettingsController@save'
		]);

		$router->get('markets/etsy/settings/all', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\SettingsController@all'
		]);

		$router->get('markets/etsy/settings/shops', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\SettingsController@getShops'
		]);

		/** Shipping Profiles */
		$router->get('markets/etsy/shipping-profiles/imported', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\ShippingProfileController@imported'
		]);

		$router->post('markets/etsy/shipping-profiles/import', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\ShippingProfileController@import'
		]);

		$router->get('markets/etsy/shipping-profiles/correlations', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\ShippingProfileController@correlations'
		]);

		$router->get('markets/etsy/shipping-profiles/parcel-service-presets', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\ShippingProfileController@parcelServicePresets'
		]);

		$router->post('markets/etsy/shipping-profiles/correlate', [
			'middleware' => 'oauth',
			'uses'       => 'Etsy\Controllers\ShippingProfileController@correlate'
		]);
	}
}
