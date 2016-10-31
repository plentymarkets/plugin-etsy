<?php
namespace Etsy\Migrations;

use Etsy\Models\Settings;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

/**
 * Class CreateSettingsTable
 */
class CreateSettingsTable
{
	/**
	 * @param Migrate $migrate
	 */
	public function run(Migrate $migrate)
	{
		$migrate->createTable(Settings::class, 10, 20);
	}
}