import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { Observable } from 'rxjs';

import { Configuration } from './configuration';
import { CreateHashlist, Hashlist } from '../model/hashlist';

@Injectable({
    providedIn: 'root'
})
export class HashlistService {

    private endpoint = Configuration.BASE_URL + '/hashlists';
    private accessKey = Configuration.ACCESS_KEY;

    constructor(private http: HttpClient) { }

    createHashlist(hashlist: CreateHashlist): Observable<number> {
        return this.http.post<number>(this.endpoint, hashlist, {
            params: {
                api_key: this.accessKey
            }
        });
    }
};