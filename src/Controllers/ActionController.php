<?php

namespace Etsy\Controllers;

use Etsy\Helper\SettingsHelper;
use Etsy\Services\Batch\Item\ItemExportService;
use Etsy\Services\Batch\Item\ItemUpdateStockService;
use Etsy\Services\Order\OrderImportService;
use Plenty\Modules\Item\Property\Contracts\PropertyGroupRepositoryContract;
use Plenty\Modules\Item\Property\Contracts\PropertyRepositoryContract;
use Plenty\Modules\Item\Property\Models\Property;
use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Modules\Market\Settings\Models\Settings;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

use Etsy\Services\Property\PropertyImportService;
use Plenty\Repositories\Models\PaginatedResult;

/**
 * Class ActionController
 */
class ActionController extends Controller
{
	/**
	 * Export items.
	 */
	public function itemExport()
	{
		pluginApp(ItemExportService::class)->run();
	}

	/**
	 * Update stock.
	 */
	public function stockUpdate()
	{
		pluginApp(ItemUpdateStockService::class)->run();
	}

	/**
	 * Import orders.
	 *
	 * @param Request $request
	 */
	public function orderImport(Request $request)
	{
		pluginApp(OrderImportService::class)->run($request->get('from', date('Y-m-d H:i:s')), $request->get('to', date('Y-m-d H:i:s')));
	}
}