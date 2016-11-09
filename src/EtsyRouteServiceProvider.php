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

		$router->get('etsy/auth', [ 'uses' => 'Etsy\Controllers\AuthController@showLogin']);
		$router->get('etsy/auth-token', ['uses' => 'Etsy\Controllers\AuthController@getToken']);
        $router->get('etsy/taxonomies/{id}', ['middleware' => 'oauth', 'uses' => 'Etsy\Controllers\TaxonomyController@showEtsyTaxonomy']);
        $router->get('etsy/taxonomies', ['middleware' => 'oauth', 'uses' => 'Etsy\Controllers\TaxonomyController@allEtsyTaxonomies']); // TODO save

		// Settings
		$router->post('etsy/settings', ['middleware' => 'oauth', 'uses' => 'Etsy\Controllers\SettingsController@saveAll']);
		$router->get('etsy/settings', ['middleware' => 'oauth','uses' => 'Etsy\Controllers\SettingsController@getAll']);


		// Shipping Profiles
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
