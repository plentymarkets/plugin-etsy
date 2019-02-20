<?php

namespace Etsy\DataProviders;


use Etsy\Contracts\TaxonomyRepositoryContract;
use Plenty\Modules\Catalog\DataProviders\NestedKeyDataProvider;

class EtsyCategoryDataProvider extends NestedKeyDataProvider
{
    /**
     * @var TaxonomyRepositoryContract $taxonomyRepository
     */
    protected $taxonomyRepository;

    public function __construct(TaxonomyRepositoryContract $taxonomyRepository)
    {
        $this->taxonomyRepository = $taxonomyRepository;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return 'categories[]';
    }

    /**
     * @inheritdoc
     */
    public function getRows(): array
    {
       return $this->loadCategories(['level' => 0]);
    }

    /**
     * @return array
     */
    public function getNestedRows($parentId): array
    {
        return $this->loadCategories(['parentId' => $parentId]);

    }

    protected function loadCategories($filter = []) {
        $categories = $this->taxonomyRepository->all($filter);
        $rows = [];

        foreach ($categories as $category) {
            $rows[] = [
                'value' => $category->id,
                'label' => $category->nameDe,
                'required' => false,
                'hasChildren' => !$category->isLeaf
            ];
        }

        return $rows;
    }
}

