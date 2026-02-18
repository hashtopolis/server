<?php

namespace Hashtopolis\inc\defines;

class UResponseTask extends UResponse {
  const TASKS              = "tasks";
  const TASKS_ID           = "taskId";
  const TASKS_SUPERTASK_ID = "supertaskId";
  const TASKS_NAME         = "name";
  const TASKS_TYPE         = "type";
  const TASKS_HASHLIST     = "hashlistId";
  const TASKS_PRIORITY     = "priority";
  const TASKS_MAX_AGENTS   = "maxAgents";
  const TASKS_IS_COMPLETE  = "isComplete";
  
  const TASK_ID                   = "taskId";
  const TASK_NAME                 = "name";
  const TASK_ATTACK               = "attack";
  const TASK_CHUNKSIZE            = "chunksize";
  const TASK_COLOR                = "color";
  const TASK_BENCH_TYPE           = "benchmarkType";
  const TASK_STATUS               = "statusTimer";
  const TASK_PRIORITY             = "priority";
  const TASK_MAX_AGENTS           = "maxAgents";
  const TASK_CPU_ONLY             = "isCpuOnly";
  const TASK_SMALL                = "isSmall";
  const TASK_ARCHIVED             = "isArchived";
  const TASK_SKIP                 = "skipKeyspace";
  const TASK_KEYSPACE             = "keyspace";
  const TASK_DISPATCHED           = "dispatched";
  const TASK_SEARCHED             = "searched";
  const TASK_SPEED                = "speed";
  const TASK_HASHLIST             = "hashlistId";
  const TASK_IMAGE                = "imageUrl";
  const TASK_FILES                = "files";
  const TASK_FILES_ID             = "fileId";
  const TASK_FILES_NAME           = "filename";
  const TASK_FILES_SIZE           = "size";
  const TASK_AGENTS               = "agents";
  const TASK_AGENTS_ID            = "agentId";
  const TASK_AGENTS_BENCHMARK     = "benchmark";
  const TASK_AGENTS_SPEED         = "speed";
  const TASK_CHUNKS               = "chunkIds";
  const TASK_USE_PREPROCESSOR     = "usePreprocessor";
  const TASK_PREPROCESSOR_ID      = "preprocessorId";
  const TASK_PREPROCESSOR_COMMAND = "preprocessorCommand";
  
  const SUBTASKS = "subtasks";
  
  const PRETASKS            = "pretasks";
  const PRETASKS_ID         = "pretaskId";
  const PRETASKS_NAME       = "name";
  const PRETASKS_PRIORITY   = "priority";
  const PRETASKS_MAX_AGENTS = "maxAgents";
  
  const PRETASK_ID         = "pretaskId";
  const PRETASK_NAME       = "name";
  const PRETASK_ATTACK     = "attackCmd";
  const PRETASK_CHUNKSIZE  = "chunksize";
  const PRETASK_COLOR      = "color";
  const PRETASK_BENCH_TYPE = "benchmarkType";
  const PRETASK_STATUS     = "statusTimer";
  const PRETASK_PRIORITY   = "priority";
  const PRETASK_MAX_AGENTS = "maxAgents";
  const PRETASK_CPU_ONLY   = "isCpuOnly";
  const PRETASK_SMALL      = "isSmall";
  const PRETASK_FILES      = "files";
  const PRETASK_FILES_ID   = "fileId";
  const PRETASK_FILES_NAME = "filename";
  const PRETASK_FILES_SIZE = "size";
  
  const SUPERTASKS      = "supertasks";
  const SUPERTASKS_ID   = "supertaskId";
  const SUPERTASKS_NAME = "name";
  
  const SUPERTASK_ID         = "supertaskId";
  const SUPERTASK_NAME       = "name";
  const SUPERTASK_MAX_AGENTS = "maxAgents";
  
  const CHUNK_ID         = "chunkId";
  const CHUNK_START      = "start";
  const CHUNK_LENGTH     = "length";
  const CHUNK_CHECKPOINT = "checkpoint";
  const CHUNK_PROGRESS   = "progress";
  const CHUNK_TASK       = "taskId";
  const CHUNK_AGENT      = "agentId";
  const CHUNK_DISPATCHED = "dispatchTime";
  const CHUNK_ACTIVITY   = "lastActivity";
  const CHUNK_STATE      = "state";
  const CHUNK_CRACKED    = "cracked";
  const CHUNK_SPEED      = "speed";
  
  const CRACKED       = "cracked";
  const IS_COMPLETE   = "isComplete";
  const WORK_POSSIBLE = "workPossible";
}