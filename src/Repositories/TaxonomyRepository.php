<?php

namespace Etsy\Repositories;

use Etsy\Contracts\CategoryRepositoryContract;
use Etsy\Contracts\TaxonomyRepositoryContract;
use Etsy\Factories\TaxonomyDataProviderFactory;
use Etsy\Helper\SettingsHelper;
use Etsy\Models\Taxonomy;
use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;

/**
 * Class TaxonomyRepository
 */
class TaxonomyRepository implements TaxonomyRepositoryContract
{
    /**
     * @var TaxonomyDataProviderFactory
     */
    private $factory;

    /**
     * @param TaxonomyDataProviderFactory $factory
     */
    public function __construct(TaxonomyDataProviderFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function get(int $taxonomyId, string $lang, array $with = []): Taxonomy
    {
        $taxonomyDataProvider = $this->factory->make($lang);

        $taxonomies = $taxonomyDataProvider->fetch();

        $taxonomy = $this->searchByTaxonomyId($taxonomyId, $taxonomies, $with);

        if (!$taxonomy instanceof Taxonomy) {
            throw new \Exception('Not data found for the given taxonomy ID');
        }

        if (in_array('path', $with) && $taxonomy->parentId !== 0) {
            $taxonomy->path = array_reverse($this->getPath([], $taxonomy->id, $lang));
        }

        return $taxonomy;
    }

    /**
     * @inheritdoc
     */
    public function all(array $filters = [], array $with = [])
    {
        $lang = 'de';

        if (isset($filters['lang'])) {
            $lang = $filters['lang'];
        }

        $taxonomyDataProvider = $this->factory->make($lang);

        $taxonomies = $taxonomyDataProvider->fetch();

        return $this->getAllTaxonomies($taxonomies, $with);
    }

    /**
     * @inheritDoc
     */
    public function getCorrelations(string $lang): array
    {
        $list = [];

        /** @var SettingsCorrelationFactory $settingsCorrelationFactory */
        $settingsCorrelationFactory = pluginApp(SettingsCorrelationFactory::class);

        /** @var TaxonomyRepositoryContract $taxonomyRepo */
        $taxonomyRepo = pluginApp(TaxonomyRepositoryContract::class);

        /** @var CategoryRepositoryContract $categoryRepo */
        $categoryRepo = pluginApp(CategoryRepositoryContract::class);

        /** @var SettingsRepositoryContract $settingsRepo */
        $settingsRepo = pluginApp(SettingsRepositoryContract::class);

        $correlations = $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_CATEGORY)
                                                   ->all(SettingsHelper::PLUGIN_NAME);

        foreach ($correlations as $correlationData) {

            $settings = $settingsRepo->get($correlationData['settingsId']);

            if (isset($settings->settings['id'])) {
                $list[] = [
                    'taxonomy' => $taxonomyRepo->get((int)$settings->settings['id'], $lang, ['path']),
                    'category' => $categoryRepo->get((int)$correlationData['categoryId'], $lang, ['path']),
                ];
            }
        }

        return $list;
    }

    /**
     * @inheritDoc
     */
    public function saveCorrelations(array $correlations, string $lang)
    {
        /** @var SettingsRepositoryContract $settingsRepo */
        $settingsRepo = pluginApp(SettingsRepositoryContract::class);

        /** @var SettingsCorrelationFactory $settingsCorrelationFactory */
        $settingsCorrelationFactory = pluginApp(SettingsCorrelationFactory::class);

        $settingsRepo->deleteAll(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_CATEGORY);

        $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_CATEGORY)
                                   ->clear(SettingsHelper::PLUGIN_NAME);

        foreach ($correlations as $correlationData) {
            if (isset($correlationData['taxonomy']) && isset($correlationData['taxonomy']['id']) && isset($correlationData['category']) && isset($correlationData['category']['id'])) {
                /** @var SettingsRepositoryContract $settingsRepo */
                $settingsRepo = pluginApp(SettingsRepositoryContract::class);

                /** @var TaxonomyRepositoryContract $taxonomyRepo */
                $taxonomyRepo = pluginApp(TaxonomyRepositoryContract::class);

                $taxonomy = $taxonomyRepo->get($correlationData['taxonomy']['id'], $lang);

                $settings = $settingsRepo->create(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_CATEGORY, [
                    'id'       => $taxonomy->id,
                    'parentId' => $taxonomy->parentId,
                    'name'     => $taxonomy->name,
                    'children' => $taxonomy->children,
                    'isLeaf'   => $taxonomy->isLeaf,
                    'level'    => $taxonomy->level,
                    'path'     => $taxonomy->path
                ]);

                $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_CATEGORY)
                                           ->createRelation($settings->id, $correlationData['category']['id']);
            }
        }
    }


    /**
     * Get all taxonomies recursively with children
     *
     * @param array $taxonomies
     * @param array $with
     *
     * @return array
     */
    private function getAllTaxonomies(array $taxonomies, array $with = [])
    {
        $list = [];

        foreach ($taxonomies as $taxonomyData) {

            /** @var Taxonomy $taxonomy */
            $taxonomy = pluginApp(Taxonomy::class);

            $taxonomy->fillByAttributes([
                'id'         => $taxonomyData['id'],
                'name'       => $taxonomyData['name'],
                'level'      => $taxonomyData['level'],
                'parentId'   => (int)$taxonomyData['parent_id'],
                'categoryId' => (int)$taxonomyData['category_id'],
                'isLeaf'     => !(isset($taxonomyData['children']) && count($taxonomyData['children'])),
                'children'   => [],
                'path'       => [],
            ]);

            if (in_array('children', $with) && isset($taxonomyData['children']) && count($taxonomyData['children'])) {
                $taxonomy->children = $this->getAllTaxonomies($taxonomyData['children'], $with);
            }

            $list[] = $taxonomy;
        }

        return $list;
    }

    /**
     *
     *
     * @param int   $taxonomyId
     * @param array $taxonomies
     * @param array $with
     *
     * @return Taxonomy|null
     */
    private function searchByTaxonomyId($taxonomyId, array $taxonomies, array $with = [])
    {
        $taxonomy = null;

        foreach ($taxonomies as $taxonomyData) {
            if ($taxonomyData['id'] == $taxonomyId) {
                /** @var Taxonomy $taxonomy */
                $taxonomy = pluginApp(Taxonomy::class);

                $taxonomy->fillByAttributes([
                    'id'         => $taxonomyData['id'],
                    'name'       => $taxonomyData['name'],
                    'level'      => $taxonomyData['level'],
                    'parentId'   => (int)$taxonomyData['parent_id'],
                    'categoryId' => (int)$taxonomyData['category_id'],
                    'isLeaf'     => !(isset($taxonomyData['children']) && count($taxonomyData['children'])),
                    'children'   => [],
                    'path'       => [],
                ]);

                if (in_array('children', $with) && isset($taxonomyData['children']) && count($taxonomyData['children'])) {
                    $taxonomy->children = $this->getAllTaxonomies($taxonomyData['children'], $with);
                }

                return $taxonomy;

            } elseif (isset($taxonomyData['children']) && count($taxonomyData['children'])) {
                $taxonomy = $this->searchByTaxonomyId($taxonomyId, $taxonomyData['children'], $with);

                if ($taxonomy instanceof Taxonomy) {
                    return $taxonomy;
                }
            }
        }

        return null;
    }

    /**
     * Recursively get category paths.
     *
     * @param array  $paths
     * @param int    $taxonomyId
     * @param string $lang
     *
     * @return array
     */
    private function getPath(array $paths, int $taxonomyId, string $lang)
    {
        $taxonomy = $this->get($taxonomyId, $lang);

        $paths[] = $taxonomy;

        if ($taxonomy->parentId !== 0) {
            return $this->getPath($paths, $taxonomy->parentId, $lang);
        }

        return $paths;
    }
}
