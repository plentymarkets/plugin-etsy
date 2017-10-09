<?php

namespace Etsy\Controllers;

use Etsy\Helper\SettingsHelper;
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
            'is_supply',
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

        $list = $settingsRepository->find(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_PROPERTY);

        if (count($list)) {
            /** @var Settings $settings */
            foreach ($list as $settings) {
                if (isset($settings->settings['propertyKey']) && isset($settings->settings['propertyValueKey'])) {
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
        /** @var PropertyRepositoryContract $propertyRepo */
        $propertyRepo = pluginApp(PropertyRepositoryContract::class);

        $list    = [];
        $page    = 0;
        $perPage = 100;

        do {
            $page++;

            /** @var PaginatedResult $result */
            $result = $propertyRepo->all(['*'], $perPage, $page);

            if ($result instanceof PaginatedResult) {
                /** @var Property $property */
                foreach ($result->getResult() as $propertyItem) {
                    $list[] = [
                        'id'        => $propertyItem->id,
                        'name'      => $propertyItem->backendName,
                        'groupId'   => $propertyItem->propertyGroupId,
                        'groupName' => $this->getPropertyGroupName($propertyItem->propertyGroupId),
                    ];
                }
            }
        } while (($result->getTotalCount()) > 0 && $page < ($result->getTotalCount() / $perPage));

        return $list;
    }

    /**
     * Correlate settings IDs with an property IDs.
     *
     * @param SettingsCorrelationFactory $settingsCorrelationFactory
     */
    public function correlate(SettingsCorrelationFactory $settingsCorrelationFactory)
    {
        $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)->clear(SettingsHelper::PLUGIN_NAME);

        foreach ($this->request->get('correlations', []) as $correlationData) {
            if (isset($correlationData['settingsId']) && $correlationData['settingsId'] && isset($correlationData['propertyId']) && $correlationData['propertyId']) {
                $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)->createRelation($correlationData['settingsId'],
                    $correlationData['propertyId']);
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
        $correlations = $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)->all(SettingsHelper::PLUGIN_NAME);

        return $correlations;
    }

    /**
     * Calculate a group Id.
     *
     * @param string $propertyKey
     *
     * @return int
     */
    private function calculateGroupId($propertyKey): int
    {
        $found = array_search($propertyKey, $this->groupIdList);

        if ($found === false) {
            $this->groupIdList[] = $propertyKey;

            return $this->calculateGroupId($propertyKey);
        }

        return $found;
    }

    /**
     * Get the property group backend name.
     *
     * @param int $propertyGroupId
     *
     * @return string
     */
    private function getPropertyGroupName($propertyGroupId)
    {
        try {
            /** @var PropertyGroupRepositoryContract $propertyGroupRepo */
            $propertyGroupRepo = pluginApp(PropertyGroupRepositoryContract::class);

            $propertyGroup = $propertyGroupRepo->findById($propertyGroupId);

            return $propertyGroup->backendName;
        } catch (\Exception $ex) {
            return $propertyGroupId;
        }
    }
}