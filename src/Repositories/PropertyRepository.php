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
            /** @var Settings $settings */
            foreach ($list as $settings) {
                if (isset($settings->settings['propertyKey']) && isset($settings->settings['propertyValueKey'])) {

                    /** @var Property $property */
                    $property = pluginApp(Property::class);

                    $property->fillByAttributes([
                        'id'        => $settings->id,
                        'name'      => $settings->settings['propertyValueName'][$lang],
                        'groupId'   => $this->calculateGroupId($settings->settings['propertyKey'][$lang]),
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
     * @inheritdoc
     */
    public function getCorrelations(string $lang): array
    {
        $list = [];

        /** @var SettingsCorrelationFactory $settingsCorrelationFactory */
        $settingsCorrelationFactory = pluginApp(SettingsCorrelationFactory::class);

        /** @var PlentyPropertyRepositoryContract $propertyRepo */
        $propertyRepo = pluginApp(PlentyPropertyRepositoryContract::class);

        /** @var SettingsRepositoryContract $settingsRepo */
        $settingsRepo = pluginApp(SettingsRepositoryContract::class);

        $correlations = $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)
                                                   ->all(SettingsHelper::PLUGIN_NAME);

        foreach ($correlations as $correlationData) {
            $settings = $settingsRepo->get($correlationData['settingsId']);

            $propertyItem = $propertyRepo->findById($correlationData['propertyId']);

            /** @var SystemProperty $systemProperty */
            $systemProperty = pluginApp(SystemProperty::class);

            $systemProperty->fillByAttributes([
                'id'        => $propertyItem->id,
                'name'      => $propertyItem->backendName,
                'groupId'   => $propertyItem->propertyGroupId,
                'groupName' => $this->getPropertyGroupName($propertyItem->propertyGroupId),
            ]);


            /** @var Property $property */
            $property = pluginApp(Property::class);

            $property->fillByAttributes([
                'id'        => $settings->id,
                'name'      => $settings->settings['propertyValueName'][$lang],
                'groupId'   => $this->calculateGroupId($settings->settings['propertyKey'][$lang]),
                'groupName' => $settings->settings['propertyName'][$lang],
            ]);

            $list[] = [
                'property'       => $property,
                'systemProperty' => $systemProperty,
            ];
        }

        return $list;
    }

    /**
     * @inheritdoc
     */
    public function saveCorrelations(array $correlations)
    {
        /** @var SettingsCorrelationFactory $settingsCorrelationFactory */
        $settingsCorrelationFactory = pluginApp(SettingsCorrelationFactory::class);

        $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)
                                   ->clear(SettingsHelper::PLUGIN_NAME);

        foreach ($correlations as $correlationData) {
            if (isset($correlationData['property']) && isset($correlationData['property']['id']) && isset($correlationData['systemProperty']) && isset($correlationData['systemProperty']['id'])) {
                $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)
                                           ->createRelation($correlationData['property']['id'], $correlationData['systemProperty']['id']);
            }
        }
    }

    /**
     * Calculate a group Id.
     *
     * @param string $propertyKey
     *
     * @return int
     */
    private function calculateGroupId(string $propertyKey): int
    {
        switch ($propertyKey) {
            case 'is_supply':
                return Property::GROUP_IS_SUPPLY;
            case 'occasion':
                return Property::GROUP_OCCASION;
            case 'when_made':
                return Property::GROUP_WHEN_MADE;
            case 'recipient':
                return Property::GROUP_RECIPIENT;
            case 'who_made':
                return Property::GROUP_WHO_MADE;
            case 'style':
                return Property::GROUP_STYLE;
            default:
                return Property::GROUP_UNKNOWN;
        }
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
