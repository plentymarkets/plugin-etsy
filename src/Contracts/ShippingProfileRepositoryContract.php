<?php

namespace Etsy\Contracts;

use Plenty\Modules\Market\Settings\Models\Settings;

/**
 * Interface ShippingProfileRepositoryContract
 */
interface ShippingProfileRepositoryContract
{
	/**
	 * @param int $id
	 * @return Settings
	 */
	public function show($id);

	/**
	 * @param array $data
	 * @return null|Settings
	 */
	public function create(array $data);

	/**
	 * @param int $settingsId
	 * @param int $parcelServicePresetId
	 * @return void
	 */
	public function createRelation($settingsId, $parcelServicePresetId);
}
