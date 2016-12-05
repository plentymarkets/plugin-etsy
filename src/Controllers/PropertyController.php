<?php

namespace Etsy\Controllers;

use Plenty\Modules\Item\Property\Contracts\PropertyItemRepositoryContract;
use Plenty\Modules\Item\Property\Models\PropertyItem;
use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Modules\Market\Settings\Models\Settings;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

use Etsy\Services\Property\PropertyImportService;
use Plenty\Repositories\Models\PaginatedResult;

/**
 * Class PropertyController
 */
class PropertyController extends Controller
{
	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var array
	 */
	private $groupIdList = [];

	/**
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Import market properties.
	 *
	 * @param PropertyImportService $service
	 */
	public function import(PropertyImportService $service)
	{
		$service->run($this->request->get('properties', [
			'occasion',
			'when_made',
			'recipient',
			'who_made',
			'style',
		]), $this->request->get('force', false) === "true");

		return pluginApp(Response::class)->make('', 204);
	}

	/**
	 * Get the imported properties.
	 *
	 * @return array
	 */
	public function imported()
	{
		$lang = $this->request->get('lang', 'de');

		$nameList = [];

		/** @var SettingsRepositoryContract $settingsRepository */
		$settingsRepository = pluginApp(SettingsRepositoryContract::class);

		$list = $settingsRepository->find('EtsyIntegrationPlugin', SettingsCorrelationFactory::TYPE_PROPERTY);

		if(count($list))
		{
			/** @var Settings $settings */
			foreach($list as $settings)
			{
				if(isset($settings->settings['propertyKey']) && isset($settings->settings['propertyValueKey']))
				{
					$nameList[] = [
						'id'        => $settings->id,
						'name'      => $settings->settings['propertyValueName'][$lang],
						'groupId'   => $this->calculateGroupId($settings->settings['propertyKey'][$lang]),
						'groupName' => $settings->settings['propertyName'][$lang],
					];
				}
			}
		}

		return $nameList;
	}

	/**
	 * Get the system properties.
	 *
	 * @return array
	 */
	public function properties()
	{
		/** @var PropertyItemRepositoryContract $propertyItemRepository */
		$propertyItemRepository = pluginApp(PropertyItemRepositoryContract::class);

		$list    = [];
		$page    = 0;
		$perPage = 100;

		do
		{
			$page ++;

			/** @var PaginatedResult $result */
			$result = $propertyItemRepository->all(['*'], $perPage, $page);

			/** @var PropertyItem $propertyItem */
			foreach($result->getResult() as $propertyItem)
			{
				$list[] = [
					'id'        => $propertyItem->id,
					'name'      => $propertyItem->backendName,
					'groupId'   => $propertyItem->propertyGroupId,
					'groupName' => $propertyItem->propertyGroupId, // TODO get group name
				];
			}
		} while(($result->getTotalCount()) > 0 && $page < ($result->getTotalCount() / $perPage));

		return $list;
	}

	/**
	 * Correlate settings IDs with an property IDs.
	 *
	 * @param SettingsCorrelationFactory $settingsCorrelationFactory
	 */
	public function correlate(SettingsCorrelationFactory $settingsCorrelationFactory)
	{
		$settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)->clear('EtsyIntegrationPlugin');

		foreach($this->request->get('correlations', []) as $correlationData)
		{
			if(isset($correlationData['settingsId']) && $correlationData['settingsId'] && isset($correlationData['propertyId']) && $correlationData['propertyId'])
			{
				$settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)->createRelation($correlationData['settingsId'], $correlationData['propertyId']);
			}
		}

		return pluginApp(Response::class)->make('', 204);
	}

	/**
	 * Get the property correlations.
	 *
	 * @param SettingsCorrelationFactory $settingsCorrelationFactory
	 *
	 * @return array
	 */
	public function correlations(SettingsCorrelationFactory $settingsCorrelationFactory)
	{
		$correlations = $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)->all('EtsyIntegrationPlugin');

		return $correlations;
	}

	/**
	 * Calculate a group Id.
	 *
	 * @param string $propertyKey
	 *
	 * @return int
	 */
	private function calculateGroupId($propertyKey):int
	{
		$found = array_search($propertyKey, $this->groupIdList);

		if($found === false)
		{
			$this->groupIdList[] = $propertyKey;

			return $this->calculateGroupId($propertyKey);
		}

		return $found;
	}
}