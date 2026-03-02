<?php

namespace Hashtopolis\inc\defines;

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
  
  const SET_TOP_PRIORITY      = "setTopPriority";
  const SET_TOP_PRIORITY_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const CREATE_TASK      = "createTask";
  const CREATE_TASK_PERM = DAccessControl::CREATE_TASK_ACCESS;
  
  const DELETE_SUPERTASK      = "deleteSupertask";
  const DELETE_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const SET_SUPERTASK_PRIORITY      = "setSupertaskPriority";
  const SET_SUPERTASK_PRIORITY_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
  const SET_SUPERTASK_MAX_AGENTS      = "setSupertaskMaxAgents";
  const SET_SUPERTASK_MAX_AGENTS_PERM = DAccessControl::MANAGE_TASK_ACCESS;
  
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
  
  const SET_STATUS_TIMER      = "setStatusTimer";
  const SET_STATUS_TIMER_PERM = DAccessControl::MANAGE_TASK_ACCESS;
}