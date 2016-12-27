import { ViewContainerRef } from '@angular/core';
import { Locale } from 'angular2localization';
import { LocaleService } from "angular2localization/angular2localization";
import { LocalizationService } from "angular2localization/angular2localization";
import { ComponentsHelper } from 'ng2-bootstrap';
export declare class EtsyComponent extends Locale {
    private _viewContainerRef;
    private _componentsHelper;
    private _viewContainerReference;
    constructor(locale: LocaleService, localization: LocalizationService, _viewContainerRef: ViewContainerRef, _componentsHelper: ComponentsHelper);
    private action;
    private _isLoading;
    private getUrlVars();
    reload(): void;
    isLoading: boolean;
    callStatusEvent(message: any, type: any): void;
    callLoadingEvent(isLoading: boolean): void;
}
