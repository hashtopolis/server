<?php

class DPretaskAction {
  const SET_PRIORITY      = "setPriority";
  const SET_PRIORITY_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;

  const SET_MAX_AGENTS      = "setMaxAgents";
  const SET_MAX_AGENTS_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const DELETE_PRETASK      = "deletePretask";
  const DELETE_PRETASK_PERM = DAccessControl::CREATE_PRETASK_ACCESS;
  
  const RENAME_PRETASK      = "renamePretask";
  const RENAME_PRETASK_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const SET_TIME      = "setTime";
  const SET_TIME_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const SET_COLOR      = "setColor";
  const SET_COLOR_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const SET_CPU_TASK      = "setCpuTask";
  const SET_CPU_TASK_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const SET_SMALL_TASK      = "setSmallTask";
  const SET_SMALL_TASK_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const CREATE_TASK      = "createTask";
  const CREATE_TASK_PERM = DAccessControl::CREATE_PRETASK_ACCESS;
  
  const CHANGE_ATTACK      = "changeAttack";
  const CHANGE_ATTACK_PERM = DAccessControl::CREATE_PRETASK_ACCESS;
}

class DTaskTypes {
  const NORMAL    = 0;
  const SUPERTASK = 1;
}

class DTaskStaticChunking {
  const NORMAL     = 0;
  const CHUNK_SIZE = 1;
  const NUM_CHUNKS = 2;
}

class DSupertaskAction {
  const DELETE_SUPERTASK      = "deleteSupertask";
  const DELETE_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const CREATE_SUPERTASK      = "createSupertask";
  const CREATE_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const APPLY_SUPERTASK      = "applySupertask";
  const APPLY_SUPERTASK_PERM = DAccessControl::RUN_TASK_ACCESS;
  
  const IMPORT_SUPERTASK      = "importSupertask";
  const IMPORT_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const BULK_SUPERTASK      = "bulkSupertaskCreation";
  const BULK_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const REMOVE_PRETASK_FROM_SUPERTASK      = "removePretaskFromSupertask";
  const REMOVE_PRETASK_FROM_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const ADD_PRETASK_TO_SUPERTASK      = "addPretaskToSupertask";
  const ADD_PRETASK_TO_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
}

class DTaskAction {
  const SET_BENCHMARK      = "setBenchmark";
  const SET_BENCHMARK_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const SET_SMALL_TASK      = "setSmallTask";
  const SET_SMALL_TASK_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const SET_CPU_TASK      = "setCpuTask";
  const SET_CPU_TASK_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const ABORT_CHUNK      = "abortChunk";
  const ABORT_CHUNK_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const RESET_CHUNK      = "resetChunk";
  const RESET_CHUNK_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const PURGE_TASK      = "purgeTask";
  const PURGE_TASK_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const SET_COLOR      = "setColor";
  const SET_COLOR_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const SET_TIME      = "setTime";
  const SET_TIME_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const RENAME_TASK      = "renameTask";
  const RENAME_TASK_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const DELETE_FINISHED      = "deleteFinished";
  const DELETE_FINISHED_PERM = DAccessControl::CREATE_TASK_ACCESS;
  
  const DELETE_TASK      = "deleteTask";
  const DELETE_TASK_PERM = DAccessControl::CREATE_TASK_ACCESS;
  
  const SET_PRIORITY      = "setPriority";
  const SET_PRIORITY_PERM = DAccessControl::MANAGE_TASK_ACCESS;

  const SET_MAX_AGENTS      = "setMaxAgents";
  const SET_MAX_AGENTS_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const SET_TOP_PRIORITY = "setTopPriority";
  const SET_TOP_PRIORITY_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const CREATE_TASK      = "createTask";
  const CREATE_TASK_PERM = DAccessControl::CREATE_TASK_ACCESS;
  
  const DELETE_SUPERTASK      = "deleteSupertask";
  const DELETE_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const SET_SUPERTASK_PRIORITY      = "setSupertaskPriority";
  const SET_SUPERTASK_PRIORITY_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const SET_SUPERTASK_TOP_PRIORITY      = "setSupertaskTopPriority";
  const SET_SUPERTASK_TOP_PRIORITY_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const ARCHIVE_TASK      = "archiveTask";
  const ARCHIVE_TASK_PERM = DAccessControl::CREATE_TASK_ACCESS;
  
  const ARCHIVE_SUPERTASK      = "archiveSupertask";
  const ARCHIVE_SUPERTASK_PERM = DAccessControl::CREATE_TASK_ACCESS;
  
  const CHANGE_ATTACK      = "changeAttack";
  const CHANGE_ATTACK_PERM = DAccessControl::CREATE_TASK_ACCESS;
  
  const DELETE_ARCHIVED      = "deleteArchived";
  const DELETE_ARCHIVED_PERM = DAccessControl::CREATE_TASK_ACCESS;
  
  const EDIT_NOTES      = "editNotes";
  const EDIT_NOTES_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const SET_STATUS_TIMER = "setStatusTimer";
  const SET_STATUS_TIMER_PERM = DAccessControl::MANAGE_TASK_ACCESS;
}