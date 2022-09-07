import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { map, Observable } from 'rxjs';

import { Configuration } from './configuration';
import { NormalTask } from '../model/task';

@Injectable({
    providedIn: 'root'
})
export class TaskService {

    private endpoint = Configuration.BASE_URL + '/tasks';
    private accessKey = Configuration.ACCESS_KEY;

    constructor(private http: HttpClient) { }

    getNormalTasksExpanding(...types: string[]): Observable<NormalTask[]> {
        return this.http.get<NormalTask[]>(this.endpoint + '/normal', {
            params: {
                api_key: this.accessKey,
                expand: types.join(',')
            }
        }).pipe(
            map(response => response.sort((l, r) => l.id - r.id)) // TODO: should sort in UI
        );
    }

    updateNormalTask(id: number, update: NormalTask): Observable<any> {
        return this.http.patch<number>(this.endpoint + '/normal/' + id, update, {
            params: {
                api_key: this.accessKey
            }
        });
    }

    deleteNormalTask(id: number): Observable<any> {
        return this.http.delete<number>(this.endpoint + '/normal/' + id, {
            params: {
                api_key: this.accessKey
            }
        });
    }
};