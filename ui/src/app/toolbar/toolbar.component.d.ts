import { OnInit } from '@angular/core';
import { EtsyComponent } from "../etsy-app.component";
export declare class ToolbarComponent implements OnInit {
    private etsyComponent;
    isLoading: boolean;
    breadcrumbs: string;
    constructor(etsyComponent: EtsyComponent);
    ngOnInit(): void;
}
