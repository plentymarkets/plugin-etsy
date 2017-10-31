<?php

namespace Etsy\Controllers;

use Etsy\Contracts\CategoryRepositoryContract;
use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
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

        $lang = $request->get('lang', 'de');

        $taxonomy = $taxonomyRepo->get($taxonomyId, $lang, $with);

        return $response->json($taxonomy);
    }

    /**
     * Get all taxonomies.
     *
     * @param Request $request
     *
     * @return array
     */
    public function all(Request $request)
    {
        $with = $request->get('with', []);

        if (!is_array($with) && strlen($with)) {
            $with = explode(',', $with);
        }

        /** @var TaxonomyRepositoryContract $taxonomyRepo */
        $taxonomyRepo = pluginApp(TaxonomyRepositoryContract::class);

        $taxonomies = $taxonomyRepo->all([
            'lang' => $request->get('language', 'de')
        ], $with);

        return $taxonomies;
    }

    /**
     * Get the taxonomy correlations.
     *
     * @param Request                    $request
     * @param Response                   $response
     * @param SettingsCorrelationFactory $settingsCorrelationFactory
     *
     * @return array
     */
    public function getCorrelations(
        Request $request,
        Response $response,
        SettingsCorrelationFactory $settingsCorrelationFactory
    ) {
        $list = [];

        $lang = $request->get('lang', 'de');

        $correlations = $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_CATEGORY)->all(SettingsHelper::PLUGIN_NAME);

        /** @var TaxonomyRepositoryContract $taxonomyRepo */
        $taxonomyRepo = pluginApp(TaxonomyRepositoryContract::class);

        /** @var CategoryRepositoryContract $categoryRepo */
        $categoryRepo = pluginApp(CategoryRepositoryContract::class);

        foreach ($correlations as $correlationData) {
            /** @var SettingsRepositoryContract $settingsRepo */
            $settingsRepo = pluginApp(SettingsRepositoryContract::class);

            $settings = $settingsRepo->get($correlationData['settingsId']);

            if (isset($settings->settings['id'])) {
                $list[] = [
                    'taxonomy' => $taxonomyRepo->get((int)$settings->settings['id'], $lang, ['path']),
                    'category' => $categoryRepo->get((int)$correlationData['categoryId'], $lang, ['path']),
                ];
            }
        }

        return $response->json($list);
    }

    /**
     * Correlate taxonomy IDs with category IDs.
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
        /** @var SettingsRepositoryContract $settingsRepo */
        $settingsRepo = pluginApp(SettingsRepositoryContract::class);

        $lang = $request->get('lang', 'de');

        $settingsRepo->deleteAll(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_CATEGORY);

        $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_CATEGORY)->clear(SettingsHelper::PLUGIN_NAME);

        foreach ($request->get('correlations', []) as $correlationData) {
            if (isset($correlationData['taxonomy']) && isset($correlationData['taxonomy']['id']) && isset($correlationData['category']) && isset($correlationData['category']['id'])) {
                /** @var SettingsRepositoryContract $settingsRepo */
                $settingsRepo = pluginApp(SettingsRepositoryContract::class);

                /** @var TaxonomyRepositoryContract $taxonomyRepo */
                $taxonomyRepo = pluginApp(TaxonomyRepositoryContract::class);

                $taxonomy = $taxonomyRepo->get($correlationData['taxonomy']['id'], $lang);

                $settings = $settingsRepo->create(SettingsHelper::PLUGIN_NAME,
                    SettingsCorrelationFactory::TYPE_CATEGORY, [
                        'id'       => $taxonomy->id,
                        'parentId' => $taxonomy->parentId,
                        'name'     => $taxonomy->name,
                        'children' => $taxonomy->children,
                        'isLeaf'   => $taxonomy->isLeaf,
                        'level'    => $taxonomy->level,
                        'path'     => $taxonomy->path
                    ]);

                $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_CATEGORY)->createRelation($settings->id,
                    $correlationData['category']['id']);
            }
        }

        return $response->make('', 204);
    }
}
