import { Component, OnInit } from '@angular/core';

import { AccessGroup } from '../../model/access-group';
import { AccessService } from '../../service/access.service';
import { CreateHashlist } from '../../model/hashlist';
import { HashlistService } from '../../service/hashlist.service';
import { Hashtype } from '../../model/hashtype';
import { HashtypeService } from '../../service/hashtype.service';

@Component({
  selector: 'create-hashlist',
  templateUrl: './create-hashlist.component.html'
})
export class CreateHashlistComponent implements OnInit {

  hashtypes: Hashtype[] = [];
  accessGroups: AccessGroup[] = [];
  // declare in backend?
  hashlistFormats: HashListFormat[] = [
    { id: 0, description: 'Text file' },
    { id: 1, description: 'HCCAPX file / PMKID hash' },
    { id: 2, description: 'Binary file (single hash)' }
  ];

  hashlist: CreateHashlist = {
    dataSourceType: 'paste',
    saltSeparator: ':',
    isSecret: false,
    isHexSalted: false,
    isSalted: false,
    useBrain: false,
    brainFeatures: 0
  } as CreateHashlist;

  constructor(
    private hashlistService: HashlistService, 
    private hashtypeService: HashtypeService,
    private accessService: AccessService
  ) { }

  ngOnInit(): void {
    this.hashtypeService.getHashTypes().subscribe(types => {
      this.hashtypes = types;
    });
    this.accessService.getAccessGroupsOfCurrentUser().subscribe(groups => {
      this.accessGroups = groups;
    });
  }

  createHashlist() { 
    this.hashlistService.createHashlist(this.hashlist).subscribe(id => {
      console.log('Created hashlist with id: ' + id);
    });
  }
}

interface HashListFormat {
  id: number;
  description: string;
}