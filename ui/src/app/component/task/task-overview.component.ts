import { Component, OnInit } from '@angular/core';

import { NormalTask } from '../../model/task';
import { TaskService } from '../../service/task.service';

@Component({
  selector: 'task-overview',
  templateUrl: './task-overview.component.html',
  styles: [
    'table { width: 64%; }'
  ]
})
export class TaskOverviewComponent implements OnInit {

  displayedColumns: string[] = ['id', 'name', 'hashlist', 'priority', 'maxAgents', 'action'];

  entries: NormalTask[] = [];

  constructor(private taskService: TaskService) { }

  ngOnInit(): void {
    this.taskService.getNormalTasksExpanding('hashlist').subscribe(tasks => {
      this.entries = tasks;
    });
  }

  updateEntry(entry: NormalTask) {
    this.taskService.updateNormalTask(entry.id, entry).subscribe(any => {
      // no-op
    });
  }

  deleteEntry(entry: NormalTask) {
    this.taskService.deleteNormalTask(entry.id).subscribe(any => {
      this.entries = this.entries.filter(other => other.id != entry.id);
    });
  }
}