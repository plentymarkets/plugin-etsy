<?php

namespace Etsy\Repositories;

use Etsy\Contracts\PropertyRepositoryContract;
use Etsy\Helper\SettingsHelper;
use Etsy\Models\Property;
use Etsy\Models\SystemProperty;
use Plenty\Modules\Item\Property\Contracts\PropertyGroupRepositoryContract;
use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Modules\Market\Settings\Models\Settings;
use Plenty\Modules\Item\Property\Contracts\PropertyRepositoryContract as PlentyPropertyRepositoryContract;
use Plenty\Repositories\Models\PaginatedResult;

/**
 * Class PropertyRepository
 */
class PropertyRepository implements PropertyRepositoryContract
{
    /**
     * @inheritdoc
     */
    public function all(array $filters = [], array $with = [])
    {
        $lang = 'de';

        if (isset($filters['lang'])) {
            $lang = $filters['lang'];
        }

        $nameList = [];

        /** @var SettingsRepositoryContract $settingsRepository */
        $settingsRepository = pluginApp(SettingsRepositoryContract::class);

        $list = $settingsRepository->find(SettingsHelper::PLUGIN_NAME, SettingsCorrelationFactory::TYPE_PROPERTY);

        if (count($list)) {
            $groupIdList = [];

            /** @var Settings $settings */
            foreach ($list as $settings) {
                if (isset($settings->settings['propertyKey']) && isset($settings->settings['propertyValueKey'])) {

                    /** @var Property $property */
                    $property = pluginApp(Property::class);

                    $property->fillByAttributes([
                        'id'        => $settings->id,
                        'name'      => $settings->settings['propertyValueName'][$lang],
                        'groupId'   => $this->calculateGroupId($groupIdList, $settings->settings['propertyKey'][$lang]),
                        'groupName' => $settings->settings['propertyName'][$lang],
                    ]);

                    $nameList[] = $property;
                }
            }
        }

        return $nameList;
    }

    /**
     * @inheritdoc
     */
    public function systemProperties(array $filters = [], array $with = [])
    {
        /** @var PlentyPropertyRepositoryContract $propertyRepo */
        $propertyRepo = pluginApp(PlentyPropertyRepositoryContract::class);

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

                    /** @var SystemProperty $systemProperty */
                    $systemProperty = pluginApp(SystemProperty::class);

                    $systemProperty->fillByAttributes([
                        'id'        => $propertyItem->id,
                        'name'      => $propertyItem->backendName,
                        'groupId'   => $propertyItem->propertyGroupId,
                        'groupName' => $this->getPropertyGroupName($propertyItem->propertyGroupId),
                    ]);

                    $list[] = $systemProperty;
                }
            }
        } while (($result->getTotalCount()) > 0 && $page < ($result->getTotalCount() / $perPage));

        return $list;
    }

    /**
     * Calculate a group Id.
     *
     * @param array  $groupIdList
     * @param string $propertyKey
     *
     * @return int
     */
    private function calculateGroupId(array &$groupIdList, string $propertyKey): int
    {
        $found = array_search($propertyKey, $groupIdList);

        if ($found === false) {
            $groupIdList[] = $propertyKey;

            return $this->calculateGroupId($groupIdList, $propertyKey);
        }

        return $found + 1;
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
