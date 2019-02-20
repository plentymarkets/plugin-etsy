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
        return $this->taxonomyRepository->all();
    }

    /**
     * @return array
     */
    public function getNestedRows($parentId): array
    {
        return [];
    }
}

