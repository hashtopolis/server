import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { Observable } from 'rxjs';

import { AccessGroup } from '../model/access-group';
import { Configuration } from './configuration';

@Injectable({
  providedIn: 'root'
})
export class AccessService {

  private endpoint = Configuration.BASE_URL + '/access';
  private accessKey = Configuration.ACCESS_KEY;

  constructor(private http: HttpClient) { }

  getAccessGroupsOfCurrentUser(): Observable<AccessGroup[]> {
    return this.http.get<AccessGroup[]>(this.endpoint + '/groups', {
      params: {
        api_key: this.accessKey
      }
    }); 
  }
}