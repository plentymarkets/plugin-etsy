import { OnInit } from '@angular/core';
import { SettingsService } from './service/settings.service';
import { EtsyComponent } from '../etsy-app.component';
import { Locale, LocaleService, LocalizationService } from 'angular2localization/angular2localization';
export declare class SettingsComponent extends Locale implements OnInit {
    private service;
    private etsyComponent;
    private settings;
    private isLoading;
    private exportLanguages;
    private _exportLanguagesBinding;
    private processes;
    private _processesBinding;
    private availableLanguages;
    private _availableShops;
    private _selectedShop;
    constructor(service: SettingsService, etsyComponent: EtsyComponent, locale: LocaleService, localization: LocalizationService);
    ngOnInit(): void;
    private loadSettings();
    private mapSettings(response);
    private loadShops(shopId);
    private saveSettings();
    exportLanguagesBinding: Array<string>;
    processesBinding: Array<string>;
    private getSelectedProcesses();
    private getShopList();
}
