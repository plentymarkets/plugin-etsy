import { OnInit } from '@angular/core';
import { LoginService } from "./service/login.service";
import { EtsyComponent } from "../etsy-app.component";
import { Locale } from "angular2localization/angular2localization";
import { LocaleService } from "angular2localization/angular2localization";
import { LocalizationService } from "angular2localization/angular2localization";
export declare class LoginComponent extends Locale implements OnInit {
    private service;
    private etsyComponent;
    private isAuthenticated;
    private isLoading;
    constructor(service: LoginService, etsyComponent: EtsyComponent, locale: LocaleService, localization: LocalizationService);
    ngOnInit(): void;
    private checkLoginStatus();
    private openLoginPopup();
    private reload();
}
