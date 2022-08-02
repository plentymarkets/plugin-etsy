<?php

namespace Etsy\Controllers;

use Etsy\EtsyServiceProvider;
use Etsy\Services\Batch\AbstractBatchService;
use Etsy\Services\Batch\Item\ItemExportService;
use Etsy\Services\Batch\Item\ItemUpdateStockService;
use Etsy\Services\Order\OrderImportService;
use Illuminate\Support\Collection;
use Plenty\Plugin\Log\Loggable;
use Plenty\Modules\Catalog\Contracts\CatalogRepositoryContract;
use Plenty\Modules\Catalog\Contracts\TemplateContainerContract;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

/**
 * Class ActionController
 */
class ActionController extends Controller
{
    use Loggable;

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

    /**
     * @param $ids
     */
	public function exportSpecificItems($ids) {
        $this->getLogger(__FUNCTION__)
            ->report('Start Manual Export by id', [
                'function' => 'exportSpecificItems',
                'products' => $ids
            ]);
	    $this->setItemFilter($ids);
        $this->itemExport();
    }

    /**
     * @param $ids
     */
    public function updateItemStock($ids) {
	    $this->setItemFilter($ids);
	    $this->stockUpdate();
    }

    /**
     * Sets a filter on the etsy template so that only specific items are exported
     *
     * @param $ids
     */
    protected function setItemFilter($ids) {
        /** @var CatalogRepositoryContract $catalogRepository */
        $catalogRepository = pluginApp(CatalogRepositoryContract::class);
        $catalogRepository->setFilters(['template' => AbstractBatchService::TEMPLATE]);
        $id = null;
        /** @var Collection $mappings */
        $mappings = $catalogRepository->all()->getResult();
        foreach ($mappings as $mapping) {
            $id = $mapping['id'];
            break;
        }

        $catalog = $catalogRepository->get($id);
        /** @var TemplateContainerContract $templateContainer */
        $templateContainer = pluginApp(TemplateContainerContract::class);
        $template = $templateContainer->getTemplate($catalog->template);

        $itemIds = array_map('intval', explode(',', $ids));
        $template->addFilter([
            'name' => 'item.hasIds',
            'params' => [
                [
                    'name' => 'itemIds',
                    'value' => $itemIds
                ]
            ]
        ]);
    }
}
