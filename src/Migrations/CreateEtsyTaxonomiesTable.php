<?php

namespace Etsy\Migrations;

use Etsy\Models\Taxonomy;
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