<?php

namespace Etsy\Migrations;

use Etsy\Api\Services\TaxonomyService;
use Etsy\Contracts\TaxonomyRepositoryContract;
use Etsy\Models\Taxonomy;
use PayPal\Api\Tax;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

/**
 * Class CreateEtsyTaxonomysTable
 *
 * @package Etsy\Migrations
 */
class CreateEtsyTaxonomiesTable
{
    public function run(Migrate $migrate, Taxonomy $taxonomy)
    {
        $migrate->createTable('Etsy\Models\Taxonomy');
    }
}