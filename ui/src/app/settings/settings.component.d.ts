import { OnInit } from '@angular/core';
import { SettingsService } from "./service/settings.service";
import { EtsyComponent } from "../etsy-app.component";
import { Locale } from "angular2localization/angular2localization";
import { LocaleService } from "angular2localization/angular2localization";
import { LocalizationService } from "angular2localization/angular2localization";
export declare class SettingsComponent extends Locale implements OnInit {
    private service;
    private etsyComponent;
    private settings;
    private isLoading;
    private exportLanguages;
    private processes;
    private availableLanguages;
    private availableShops;
    constructor(service: SettingsService, etsyComponent: EtsyComponent, locale: LocaleService, localization: LocalizationService);
    ngOnInit(): void;
    private loadSettings();
    private mapSettings(response);
    private loadShops(shopId);
    private saveSettings();
    private getSelectedExportLanguages();
    private getSelectedProcesses();
    private getShopList();
}
