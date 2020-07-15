<?php

namespace Etsy\Controllers;

use Etsy\Services\Batch\Item\ItemExportService;
use Etsy\Services\Batch\Item\ItemUpdateStockService;
use Etsy\Services\Order\OrderImportService;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

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