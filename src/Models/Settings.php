<?php

namespace Etsy\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class Settings
 *
 * @property string $name
 * @property string $value
 * @property string $createdAt
 * @property string $updatedAt
 */
class Settings extends Model
{
	public $id = 0;
	public $name = '';
	public $value = '';
	public $createdAt = '';
	public $updatedAt = '';

	/**
	 * @return string
	 */
	public function getTableName():string
	{
		return 'EtsyIntegrationPlugin::settings';
	}
}