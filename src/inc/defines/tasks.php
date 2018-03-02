<?php

class DPretaskAction {
  const SET_PRIORITY   = "setPriority";
  const DELETE_PRETASK = "deletePretask";
  const RENAME_PRETASK = "renamePretask";
  const SET_TIME       = "setTime";
  const SET_COLOR      = "setColor";
  const SET_CPU_TASK   = "setCpuTask";
  const SET_SMALL_TASK = "setSmallTask";
  const CREATE_TASK    = "createTask";
}

class DTaskTypes {
  const NORMAL    = 0;
  const SUPERTASK = 1;
}

class DSupertaskAction {
  const DELETE_SUPERTASK = "deleteSupertask";
  const CREATE_SUPERTASK = "createSupertask";
  const APPLY_SUPERTASK  = "applySupertask";
  const IMPORT_SUPERTASK = "importSupertask";
}

class DTaskAction {
  const SET_BENCHMARK          = "setBenchmark";
  const SET_SMALL_TASK         = "setSmallTask";
  const SET_CPU_TASK           = "setCpuTask";
  const ABORT_CHUNK            = "abortChunk";
  const RESET_CHUNK            = "resetChunk";
  const PURGE_TASK             = "purgeTask";
  const SET_COLOR              = "setColor";
  const SET_TIME               = "setTime";
  const RENAME_TASK            = "renameTask";
  const DELETE_FINISHED        = "deleteFinished";
  const DELETE_TASK            = "deleteTask";
  const SET_PRIORITY           = "setPriority";
  const CREATE_TASK            = "createTask";
  const DELETE_SUPERTASK       = "deleteSupertask";
  const SET_SUPERTASK_PRIORITY = "setSupertaskPriority";
}