<?php

namespace Etsy\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Etsy\Contracts\TaxonomyRepositoryContract;
use Plenty\Plugin\Http\Response;

/**
 * Class TaxonomyController
 */
class TaxonomyController extends Controller
{
    /**
     * Get the taxonomy data.
     *
     * @param Request  $request
     * @param Response $response
     * @param int      $taxonomyId
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function get(Request $request, Response $response, int $taxonomyId)
    {
        $with = $request->get('with', []);

        if (!is_array($with) && strlen($with)) {
            $with = explode(',', $with);
        }

        /** @var TaxonomyRepositoryContract $taxonomyRepo */
        $taxonomyRepo = pluginApp(TaxonomyRepositoryContract::class);

        $lang = 'de'; // TODO EN file must be downloaded and, due to its huge size, stored on S3 // $request->get('lang', 'de');

        $taxonomy = $taxonomyRepo->get($taxonomyId, $with);

        return $response->json($taxonomy);
    }

    /**
     * Get all taxonomies.
     *
     * @param Request $request
     * @param Response $response
     *
     * @return array
     */
    public function all(Request $request, Response $response)
    {
        $with = $request->get('with', []);

        if (!is_array($with) && strlen($with)) {
            $with = explode(',', $with);
        }

        /** @var TaxonomyRepositoryContract $taxonomyRepo */
        $taxonomyRepo = pluginApp(TaxonomyRepositoryContract::class);

        $taxonomies = $taxonomyRepo->all([
            'lang' => 'de', // // TODO EN file must be downloaded and, due to its huge size, stored on S3 // $request->get('lang', 'de'); $request->get('lang', 'de')
        ], $with);

        return $response->json($taxonomies);
    }

    /**
     * Get the taxonomy correlations.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return array
     */
    public function getCorrelations(Request $request, Response $response)
    {
        /** @var TaxonomyRepositoryContract $taxonomyRepo */
        $taxonomyRepo = pluginApp(TaxonomyRepositoryContract::class);

        $correlations = []; //todo

        return $response->json($correlations);
    }

    /**
     * Correlate taxonomy IDs with category IDs.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function saveCorrelations(Request $request, Response $response)
    {
        /** @var TaxonomyRepositoryContract $taxonomyRepo */
        $taxonomyRepo = pluginApp(TaxonomyRepositoryContract::class);

        //$taxonomyRepo->saveCorrelations($request->get('correlations', []), $request->get('lang', 'de'));

        return $response->make('', 204);
    }
}
