import { Http } from '@angular/http';
import { TerraLoadingBarService, TerraBaseService } from '@plentymarkets/terra-components';
import { Observable } from 'rxjs';
export declare class TaxonomyService extends TerraBaseService {
    constructor(loadingBarService: TerraLoadingBarService, http: Http);
    getCorrelations(): Observable<any>;
    getTaxonomies(): Observable<any>;
    getCategories(page?: number, perPage?: number): Observable<any>;
    saveCorrelations(data: any): Observable<any>;
}
