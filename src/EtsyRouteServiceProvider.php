<?php

namespace Etsy;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Etsy\Services\Order\OrderImportService;
use Etsy\Services\Shipping\ShippingProfileImportService;
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


		/** Taxonomies */
		$router->get('etsy/taxonomies/imported', [
			// 'middleware' => 'oauth',
			'uses' => 'Etsy\Controllers\TaxonomyController@imported'
		]);

		$router->get('etsy/taxonomies/categories', [
			// 'middleware' => 'oauth',
			'uses' => 'Etsy\Controllers\TaxonomyController@categories'
		]);

		$router->post('etsy/taxonomies/correlate', [
			// 'middleware' => 'oauth',
			'uses' => 'Etsy\Controllers\TaxonomyController@correlate'
		]);

		$router->get('etsy/taxonomies/correlations',  [
			// 'middleware' => 'oauth',
			'uses' => 'Etsy\Controllers\TaxonomyController@correlations'
		]);

		/** Auth */
		$router->get('etsy/auth/login-url', [
			'uses' => 'Etsy\Controllers\AuthController@getLoginUrl'
		]);

		$router->get('etsy/auth/access-token', [
			'uses' => 'Etsy\Controllers\AuthController@getAccessToken'
		]);

		$router->get('etsy/auth/status', [
			'uses' => 'Etsy\Controllers\AuthController@status'
		]);

		/** Settings */
		$router->post('etsy/settings/save', [
			// 'middleware' => 'oauth',
			'uses' => 'Etsy\Controllers\SettingsController@save'
		]);

		$router->get('etsy/settings/all', [
			// 'middleware' => 'oauth',
			'uses' => 'Etsy\Controllers\SettingsController@all'
		]);

		/** Shipping Profiles */
		$router->get('etsy/shipping-profiles/imported',  [
			// 'middleware' => 'oauth',
			'uses' => 'Etsy\Controllers\ShippingProfileController@imported'
		]);

		$router->post('etsy/shipping-profiles/import',    [
			// 'middleware' => 'oauth',
			'uses' => 'Etsy\Controllers\ShippingProfileController@import'
		]);

		$router->get('etsy/shipping-profiles/correlations',  [
			// 'middleware' => 'oauth',
			'uses' => 'Etsy\Controllers\ShippingProfileController@correlations'
		]);

		$router->get('etsy/shipping-profiles/parcel-service-presets',  [
			// 'middleware' => 'oauth',
			'uses' => 'Etsy\Controllers\ShippingProfileController@parcelServicePresets'
		]);

		$router->post('etsy/shipping-profiles/correlate',    [
			// 'middleware' => 'oauth',
			'uses' => 'Etsy\Controllers\ShippingProfileController@correlate'
		]);
	}
}
