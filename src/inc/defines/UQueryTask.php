<?php

namespace Hashtopolis\inc\defines;

class UQueryTask extends UQuery {
  const TASK_ID      = "taskId";
  const SUPERTASK_ID = "supertaskId";
  const PRETASK_ID   = "pretaskId";
  const CHUNK_ID     = "chunkId";
  
  const TASK_NAME                 = "name";
  const TASK_HASHLIST             = "hashlistId";
  const TASK_ATTACKCMD            = "attackCmd";
  const TASK_CHUNKSIZE            = "chunksize";
  const TASK_STATUS               = "statusTimer";
  const TASK_BENCHTYPE            = "benchmarkType";
  const TASK_COLOR                = "color";
  const TASK_CPU_ONLY             = "isCpuOnly";
  const TASK_SMALL                = "isSmall";
  const TASK_SKIP                 = "skip";
  const TASK_CRACKER_VERSION      = "crackerVersionId";
  const TASK_FILES                = "files";
  const TASK_PRIORITY             = "priority";
  const TASK_MAX_AGENTS           = "maxAgents";
  const TASK_PRINCE               = "isPrince";  // DEPRECATED
  const TASK_PREPROCESSOR_COMMAND = "preprocessorCommand";
  const TASK_PREPROCESSOR         = "preprocessorId";
  
  const TASK_CRACKER_TYPE    = "crackerTypeId";
  const PRETASKS             = "pretasks";
  const MASKS                = "masks";
  const TASK_OPTIMIZED       = "optimizedFlag";
  const AGENT_ID             = "agentId";
  const SUPERTASK_PRIORITY   = "supertaskPriority";
  const SUPERTASK_MAX_AGENTS = "supertaskMaxAgents";
  const SUPERTASK_NAME       = "name";
  const TASK_BASEFILES       = "basefiles";
  const TASK_ITERFILES       = "iterfiles";
  
  const PRETASK_PRIORITY   = "priority";
  const PRETASK_MAX_AGENTS = "maxAgents";
  const PRETASK_NAME       = "name";
  const PRETASK_COLOR      = "color";
  const PRETASK_CHUNKSIZE  = "chunksize";
  const PRETASK_CPU_ONLY   = "isCpuOnly";
  const PRETASK_SMALL      = "isSmall";
}