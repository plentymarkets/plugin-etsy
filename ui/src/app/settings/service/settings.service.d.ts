import { Http } from '@angular/http';
import { TerraLoadingBarService, TerraBaseService } from '@plentymarkets/terra-components';
import { Observable } from 'rxjs';
import { ShopData } from '../data/shop-data';
export declare class SettingsService extends TerraBaseService {
    constructor(loadingBarService: TerraLoadingBarService, http: Http);
    getSettings(): Observable<any>;
    saveSettings(data: any): Observable<any>;
    getShops(): Observable<ShopData>;
}
