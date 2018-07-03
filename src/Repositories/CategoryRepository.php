<?php

namespace Etsy\Repositories;

use Etsy\Models\Category;
use Plenty\Modules\Category\Contracts\CategoryBranchRepositoryContract;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract as PlentyCategoryRepositoryContract;
use Etsy\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\CategoryDetails;
use Plenty\Modules\System\Contracts\SystemInformationRepositoryContract;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;
use Plenty\Modules\System\Models\Webstore;

/**
 * Class CategoryRepository
 */
class CategoryRepository implements CategoryRepositoryContract
{
    /**
     * @inheritdoc
     */
    public function get(int $categoryId, string $lang, array $with = [])
    {
        /** @var PlentyCategoryRepositoryContract $plentyCategoryRepo */
        $plentyCategoryRepo = pluginApp(PlentyCategoryRepositoryContract::class);

        $plentyCategory = $plentyCategoryRepo->get($categoryId, $lang);
        $categoryDetailsList = $plentyCategory->details;
        /** @var CategoryDetails $categoryDetails */
        if($categoryDetailsList instanceof \ArrayAccess) {
            $categoryDetails = $categoryDetailsList->offsetGet(0);
        }

        /** @var Category $category */
        $category = pluginApp(Category::class);

        $category->fillByAttributes([
            'id'       => (int)$plentyCategory->id,
            'name'     => $categoryDetails->name,
            'level'    => (int)$plentyCategory->level - 1,
            'parentId' => (int)$plentyCategory->parentCategoryId,
            'children' => [],
            'path'     => [],
        ]);

        /** @var PlentyCategoryRepositoryContract $plentyCategoryRepo */
        $plentyCategoryRepo = pluginApp(PlentyCategoryRepositoryContract::class);

        $children = $plentyCategoryRepo->getChildren($categoryId, $lang);

        $category->isLeaf = count($children) ? false : true;

        if (in_array('children', $with)) {
            $category->children = $this->getChildren([], $categoryId, $lang);
        }

        if (in_array('path', $with)) {
            $category->path = $this->getPath($categoryId, $lang);
        }

        return $category;
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

        /** @var PlentyCategoryRepositoryContract $plentyCategoryRepo */
        $plentyCategoryRepo = pluginApp(PlentyCategoryRepositoryContract::class);
        /** @var WebstoreRepositoryContract $webstoreRepo */
        $webstoreRepo = pluginApp(WebstoreRepositoryContract::class);
        /** @var SystemInformationRepositoryContract $sysInfoRepo */
        $sysInfoRepo = pluginApp(SystemInformationRepositoryContract::class);

        /** @var Webstore $webstore */
        $webstore = $webstoreRepo->findByPlentyId($sysInfoRepo->loadValue('plentyId'));

        $categories = $plentyCategoryRepo->getLinklistTree('item', $lang, $webstore->id);

        $list = $this->buildCategoriesTree([], $categories, $lang, $with);

        return $list;
    }

    /**
     * Recursively get category children.
     *
     * @param array  $childList
     * @param int    $categoryId
     * @param string $lang
     *
     * @return array
     */
    private function getChildren(array $childList, int $categoryId, string $lang)
    {
        /** @var PlentyCategoryRepositoryContract $plentyCategoryRepo */
        $plentyCategoryRepo = pluginApp(PlentyCategoryRepositoryContract::class);

        $children = $plentyCategoryRepo->getChildren($categoryId, $lang);

        if (count($children)) {
            foreach ($children as $child) {
                /** @var Category $category */
                $category = pluginApp(Category::class);

                $category->fillByAttributes([
                    'id'       => (int)$child->id,
                    'name'     => $child->details[0]->name,
                    'level'    => (int)$child->level - 1,
                    'parentId' => (int)$child->parentCategoryId,
                    'children' => [],
                    'path'     => [],
                    'isLeaf'   => true,
                ]);

                $category->children = $this->getChildren([], $child->id, $lang);

                $category->isLeaf = $category->children ? false : true;

                $childList[] = $category;
            }
        }

        return $childList;
    }

    /**
     * Get category path.
     *
     * @param int    $categoryId
     * @param string $lang
     *
     * @return array
     */
    private function getPath(int $categoryId, string $lang)
    {
        $list = [];

        /** @var CategoryBranchRepositoryContract $categoryBranchRepo */
        $categoryBranchRepo = pluginApp(CategoryBranchRepositoryContract::class);

        $branch = $categoryBranchRepo->find($categoryId);

        if ($branch->category1Id > 0) {
            $list[] = $this->get($branch->category1Id, $lang);
        }

        if ($branch->category2Id > 0) {
            $list[] = $this->get($branch->category2Id, $lang);
        }

        if ($branch->category3Id > 0) {
            $list[] = $this->get($branch->category3Id, $lang);
        }

        if ($branch->category4Id > 0) {
            $list[] = $this->get($branch->category4Id, $lang);
        }

        if ($branch->category5Id > 0) {
            $list[] = $this->get($branch->category5Id, $lang);
        }

        if ($branch->category6Id > 0) {
            $list[] = $this->get($branch->category6Id, $lang);
        }

        return $list;
    }

    /**
     * Recursively build categories tree.
     *
     * @param array  $list
     * @param array  $plentyCategories
     * @param string $lang
     * @param array  $with
     *
     * @return array
     */
    private function buildCategoriesTree(array $list, $plentyCategories, $lang, array $with = [])
    {
        foreach ($plentyCategories as $plentyCategory) {
            /** @var Category $category */
            $category = pluginApp(Category::class);

            $category->fillByAttributes([
                'id'       => $plentyCategory->id,
                'name'     => $plentyCategory->details[0]->name,
                'level'    => $plentyCategory->level,
                'parentId' => (int)$plentyCategory->parentCategoryId,
                'children' => [],
                'path'     => [],
                'isLeaf'   => $plentyCategory->children ? false : true,
            ]);

            /** @var PlentyCategoryRepositoryContract $plentyCategoryRepo */
            $plentyCategoryRepo = pluginApp(PlentyCategoryRepositoryContract::class);

            $children = $plentyCategoryRepo->getChildren($plentyCategory->id, $lang);

            if (in_array('children', $with) && count($children)) {
                $category->children = $this->buildCategoriesTree([], $children, $lang, $with);
            }

            $list[] = $category;
        }

        return $list;
    }
}
