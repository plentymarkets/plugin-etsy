<?hh //strict
namespace Etsy\Repositories;

use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Market\Settings\Models\Settings;

use Etsy\Contracts\ShippingProfileRepositoryContract;

class ShippingProfileRepository implements ShippingProfileRepositoryContract
{
    private SettingsRepositoryContract $settingsRepository;

    private SettingsCorrelationFactory $settingsCorrelation;

    private ConfigRepository $config;

    public function __construct(
        SettingsRepositoryContract $settingsRepository,
        SettingsCorrelationFactory $settingsCorrelation,
        ConfigRepository $config
    )
    {
        $this->settingsRepository = $settingsRepository;
        $this->settingsCorrelation = $settingsCorrelation;
        $this->config = $config;
    }
    public function show(int $id):Settings
    {
        return $this->settingsRepository->get($id);
    }


    public function create(array<string,mixed> $data):Settings
    {
        if($settings = $this->getShippingProfileById((int) $data['id']))
        {
            return $settings;
        }

        $settings = $this->settingsRepository->create($this->config->get('EtsyIntegrationPlugin.marketplaceId'), SettingsCorrelationFactory::TYPE_SHIPPING, $data);

        return $settings;
    }

    public function createRelation(int $settingsId, int $parcelServicePresetId):void
    {
        $this->settingsCorrelation->type(SettingsCorrelationFactory::TYPE_SHIPPING)->createRelation($settingsId, $parcelServicePresetId);
    }

    private function getShippingProfileById(int $shippingProfileId):?Settings
    {
        $list = $this->settingsRepository->find($this->config->get('EtsyIntegrationPlugin.marketplaceId'), SettingsCorrelationFactory::TYPE_SHIPPING);

        foreach($list as $settings)
        {
            $shippingProfileSettings = $settings->settings;

            if(is_array($shippingProfileSettings) && (int) $shippingProfileSettings['id'] == $shippingProfileId)
            {
                return $settings;
            }
        }

        return null;
    }
}
