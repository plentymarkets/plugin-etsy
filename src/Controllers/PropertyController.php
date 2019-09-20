<?php

namespace Etsy\Controllers;

use Etsy\Contracts\PropertyRepositoryContract;
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
        ]), $request->get('force', true) === 'true');

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
     * Get the property correlations.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return array
     */
    public function getCorrelations(Request $request, Response $response)
    {
        /** @var PropertyRepositoryContract $propertyRepo */
        $propertyRepo = pluginApp(PropertyRepositoryContract::class);

        $correlations = $propertyRepo->getCorrelations($request->get('lang', 'de'));

        return $response->json($correlations);
    }

    /**
     * Correlate settings IDs with an property IDs.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function saveCorrelations(Request $request, Response $response)
    {
        /** @var PropertyRepositoryContract $propertyRepo */
        $propertyRepo = pluginApp(PropertyRepositoryContract::class);

        $propertyRepo->saveCorrelations($request->get('correlations', []));

        return $response->make('', 204);
    }
}