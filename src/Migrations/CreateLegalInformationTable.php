<?php
namespace Etsy\Migrations;

use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
use Etsy\Models\LegalInformation;

/**
 * Class CreateLegalInformationTable
 *
 * @package Etsy\Migrations
 */
class CreateLegalInformationTable
{
	public function run(Migrate $migrate, LegalInformation $legalInformation)
	{
		$migrate->createTable('Etsy\Models\LegalInformation');
	}
}