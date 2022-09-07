import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { Observable } from 'rxjs';

import { Configuration } from './configuration';
import { Hashtype } from '../model/hashtype';

@Injectable({
  providedIn: 'root'
})
export class HashtypeService {

  private endpoint = Configuration.BASE_URL + '/hashtypes';

  constructor(private http: HttpClient) { }

  getHashTypes(): Observable<Hashtype[]> {
    return this.http.get<Hashtype[]>(this.endpoint);
  }
}