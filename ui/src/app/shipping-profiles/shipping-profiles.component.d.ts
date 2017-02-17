import { OnInit } from '@angular/core';
import { ShippingProfileService } from './service/shipping-profile.service';
import { EtsyComponent } from "../etsy-app.component";
import { LocaleService } from "angular2localization/angular2localization";
import { LocalizationService } from "angular2localization/angular2localization";
import { Locale } from "angular2localization/angular2localization";
export declare class ShippingProfilesComponent extends Locale implements OnInit {
    private service;
    private etsyComponent;
    private parcelServicePresetList;
    private shippingProfileSettingsList;
    private shippingProfileCorrelationList;
    private isLoading;
    constructor(service: ShippingProfileService, etsyComponent: EtsyComponent, locale: LocaleService, localization: LocalizationService);
    ngOnInit(): void;
    private getParcelServiceList();
    private getShippingProfileSettingsList();
    private getShippingProfileCorrelations();
    private saveCorrelations();
    private deleteCorrelations();
    private addCorrelation();
    private import();
    private reload();
}
