import { Http } from '@angular/http';
import { TerraLoadingBarService, TerraBaseService } from '@plentymarkets/terra-components';
import { Observable } from 'rxjs';
export declare class LoginService extends TerraBaseService {
    constructor(loadingBarService: TerraLoadingBarService, http: Http);
    getLoginStatus(): Observable<any>;
    getLoginUrl(): Observable<any>;
}
