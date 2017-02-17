import { Http } from '@angular/http';
import { TerraLoadingBarService, TerraBaseService } from '@plentymarkets/terra-components';
import { Observable } from 'rxjs';
import { ParcelServicesData } from '../data/parcel-services-data';
import { ShippingProfileSettingsData } from '../data/shipping-profile-settings-data';
export declare class ShippingProfileService extends TerraBaseService {
    constructor(loadingBarService: TerraLoadingBarService, http: Http);
    getParcelServiceList(): Observable<ParcelServicesData>;
    getShippingProfileSettingsList(): Observable<ShippingProfileSettingsData>;
    getShippingProfileCorrelations(): Observable<any>;
    saveCorrelations(data: any): Observable<any>;
    importShippingProfiles(): Observable<any>;
}
