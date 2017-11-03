<?php

namespace Etsy\Controllers;

use Etsy\Contracts\PropertyRepositoryContract;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

use Etsy\Services\Property\PropertyImportService;

/**
 * Class PropertyController
 */
class PropertyController extends Controller
{
    /**
     * Import market properties.
     *
     * @param Request               $request
     * @param Response              $response
     * @param PropertyImportService $service
     *
     * @return Response
     */
    public function import(Request $request, Response $response, PropertyImportService $service)
    {
        $service->run($request->get('properties', [
            'is_supply',
            'occasion',
            'when_made',
            'recipient',
            'who_made',
            'style',
        ]), $request->get('force', false) === 'true');

        return $response->make('', 204);
    }

    /**
     * Get the imported properties.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function all(Request $request, Response $response)
    {
        /** @var PropertyRepositoryContract $propertyRepo */
        $propertyRepo = pluginApp(PropertyRepositoryContract::class);

        $properties = $propertyRepo->all([
            'lang' => $request->get('lang', 'de')
        ]);

        return $response->json($properties);
    }

    /**
     * Get the system properties.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return array
     */
    public function systemProperties(Request $request, Response $response)
    {
        /** @var PropertyRepositoryContract $propertyRepo */
        $propertyRepo = pluginApp(PropertyRepositoryContract::class);

        $properties = $propertyRepo->systemProperties([
            'lang' => $request->get('lang', 'de')
        ]);

        return $response->json($properties);
    }

    /**
     * Correlate settings IDs with an property IDs.
     *
     * @param Request                    $request
     * @param Response                   $response
     * @param SettingsCorrelationFactory $settingsCorrelationFactory
     *
     * @return Response
     */
    public function saveCorrelations(
        Request $request,
        Response $response,
        SettingsCorrelationFactory $settingsCorrelationFactory
    ) {
        $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)->clear(SettingsHelper::PLUGIN_NAME);

        foreach ($request->get('correlations', []) as $correlationData) {
            if (isset($correlationData['settingsId']) && $correlationData['settingsId'] && isset($correlationData['propertyId']) && $correlationData['propertyId']) {
                $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)->createRelation($correlationData['settingsId'],
                    $correlationData['propertyId']);
            }
        }

        return $response->make('', 204);
    }

    /**
     * Get the property correlations.
     *
     * @param Response                   $response
     * @param SettingsCorrelationFactory $settingsCorrelationFactory
     *
     * @return array
     */
    public function getCorrelations(Response $response, SettingsCorrelationFactory $settingsCorrelationFactory)
    {
        $correlations = $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)->all(SettingsHelper::PLUGIN_NAME);

        return $response->json($correlations);
    }


}