import { Http } from '@angular/http';
import { TerraLoadingBarService, TerraBaseService } from '@plentymarkets/terra-components';
import { Observable } from 'rxjs';
import { PropertyData } from "../data/property-data";
import { MarketPropertyData } from "../data/market-property-data";
export declare class PropertyService extends TerraBaseService {
    constructor(loadingBarService: TerraLoadingBarService, http: Http);
    getProperties(): Observable<PropertyData>;
    getMarketProperties(): Observable<MarketPropertyData>;
    getCorrelations(): Observable<any>;
    saveCorrelations(data: any): Observable<any>;
    importMarketProperties(): Observable<any>;
}
