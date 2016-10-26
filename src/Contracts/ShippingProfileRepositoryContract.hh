<?hh //strict
namespace Etsy\Contracts;

use Etsy\Models\ShippingProfile;
use Plenty\Modules\Market\Settings\Models\Settings;

interface ShippingProfileRepositoryContract
{
    public function show(int $id):Settings;

    public function create(array<string,mixed> $data):Settings;

    public function createRelation(int $settingsId, int $parcelServicePresetId):void;    
}
