import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule } from '@angular/common/http';
import { MatButtonModule } from '@angular/material/button';
import { MatListModule } from '@angular/material/list'; 
import { MatSelectModule } from '@angular/material/select'; 
import { MatTableModule } from '@angular/material/table';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { FlexLayoutModule } from '@angular/flex-layout';
import { FormsModule } from '@angular/forms';

import { AppComponent } from './app.component';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { CreateHashlistComponent } from './component/hashlist/create-hashlist.component';
import { TaskOverviewComponent } from './component/task/task-overview.component';

@NgModule({
  declarations: [
    AppComponent,
    CreateHashlistComponent,
    TaskOverviewComponent
  ],
  imports: [
    BrowserModule,
    FlexLayoutModule,
    FormsModule,
    HttpClientModule,
    MatButtonModule,
    MatListModule,
    MatSelectModule,
    MatIconModule,
    MatInputModule,
    MatTableModule,
    NoopAnimationsModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }