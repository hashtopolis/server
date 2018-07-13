<?php

// user api defines (these are started with 'U')

#####################
# Query definitions #
#####################

abstract class UQuery { // include only generalized query values
  const SECTION = "section";
  const REQUEST = "request";
  const ACCESS_KEY  = "accessKey";
}

class UQueryAgent extends UQuery {
  const VOUCHER = "voucher";
  const AGENT_ID = "agentId";
  const ACTIVE = "active";
  const USER = "user";
  const NAME = "name";
  const CPU_ONLY = "cpuOnly";
  const EXTRA_PARAMS = "extraParameters";
  const IGNORE_ERRORS = "ignoreErrors";
  const TRUSTED = "trusted";
}

class UQueryTask extends UQuery {
  const TASK_ID = "taskId";
}

######################
# Values definitions #
######################

abstract class UValues {
  const SUCCESS = "SUCCESS";
  const OK      = "OK";
  const NONE    = null;
  const ERROR   = "ERROR";
}



########################
# Response definitions #
########################

abstract class UResponse {
  const SECTION  = "section";
  const REQUEST  = "request";
  const RESPONSE = "response";
}

class UResponseErrorMessage extends UResponse {
  const MESSAGE = "message";
}

class UResponseAgent extends UResponse {
  const VOUCHER  = "voucher";
  const VOUCHERS = "vouchers";

  const BINARIES          = "binaries";
  const BINARIES_NAME     = "name";
  const BINARIES_URL      = "url";
  const BINARIES_VERSION  = "version";
  const BINARIES_OS       = "os";
  const BINARIES_FILENAME = "filename";
  const AGENT_URL         = "apiUrl";

  const AGENTS         = "agents";
  const AGENTS_ID      = "agentId";
  const AGENTS_NAME    = "name";
  const AGENTS_DEVICES = "devices";

  const AGENT_NAME = "name";
  const AGENT_DEVICES = "devices";
  const AGENT_OWNER = "owner";
  const AGENT_OWNER_ID = "userId";
  const AGENT_OWNER_NAME = "username";
  const AGENT_CPU_ONLY = "isCpuOnly";
  const AGENT_TRUSTED = "isTrusted";
  const AGENT_ACTIVE = "isActive";
  const AGENT_TOKEN = "token";
  const AGENT_PARAMS = "extraParameters";
  const AGENT_ERRORS = "errorFlag";
  const AGENT_ACTIVITY = "lastActivity";
  const AGENT_ACTIVITY_ACTION = "action";
  const AGENT_ACTIVITY_TIME = "time";
  const AGENT_ACTIVITY_IP = "ip";
}

class UResponseTask extends UResponse {
  const TASKS = "tasks";
  const TASKS_ID = "taskId";
  const TASKS_SUPERTASK_ID = "supertaskId";
  const TASKS_NAME = "name";
  const TASKS_TYPE = "type";
  const TASKS_HASHLIST = "hashlistId";
  const TASKS_PRIORITY = "priority";

  const TASK_ID = "taskId";
  const TASK_NAME = "name";
  const TASK_ATTACK = "attack";
  const TASK_CHUNKSIZE = "cunksize";
  const TASK_COLOR = "color";
  const TASK_BENCH_TYPE = "benchmarkType";
  const TASK_STATUS = "statusTimer";
  const TASK_PRIORITY = "priority";
  const TASK_CPU_ONLY = "isCpuOnly";
  const TASK_SMALL = "isSmall";
  const TASK_SKIP = "skipKeyspace";
  const TASK_KEYSPACE = "keyspace";
  const TASK_DISPATCHED = "dispatched";
  const TASK_SEARCHED = "searched";
  const TASK_SPEED = "speed";
  const TASK_HASHLIST = "hashlistId";
  const TASK_IMAGE = "imageUrl";
  const TASK_FILES = "files";
  const TASK_FILES_ID = "fileId";
  const TASK_FILES_NAME = "filename";
  const TASK_FILES_SIZE = "size";
  const TASK_AGENTS = "agents";
  const TASK_AGENTS_ID = "agentId";
  const TASK_AGENTS_BENCHMARK = "benchmark";
  const TASK_AGENTS_SPEED = "speed";
  const TASK_CHUNKS = "chunkIds";
}


###############################
# Section/Request definitions #
###############################

abstract class UApi {
  static function getConstants() {
    try {
      $oClass = new ReflectionClass(static::class);
    }
    catch (ReflectionException $e) {
      die("Exception: " . $e->getMessage());
    }
    return $oClass->getConstants();
  }

  static function getSection($section){
    switch($section){
      case USection::TEST:
        return new USectionTest();
      case USection::AGENT:
        return new USectionAgent();
      case USection::TASK:
        return new USectionTask();
      case USection::PRETASK:
        return new USectionPretask();
      case USection::SUPERTASK:
        return new USectionSupertask();
      case USection::HASHLIST:
        return new USectionHashlist();
      case USection::SUPERHASHLIST:
        return new USectionSuperhashlist();
      case USection::FILE:
        return new USectionFile();
      case USection::CRACKER:
        return new USectionCracker();
      case USection::CONFIG:
        return new USectionConfig();
      case USection::USER:
        return new USectionUser();
      case USection::GROUP:
        return new USectionGroup();
      case USection::ACCESS:
        return new USectionAccess();
    }
    return null;
  }

  static function getDescription($section, $constant){
    // TODO: add descriptions for sections and constants
    return "__" . $section . "_" . $constant . "__";
  }
}

class USection extends UApi {
  const TEST          = "test";
  const AGENT         = "agent";
  const TASK          = "task";
  const PRETASK       = "pretask";
  const SUPERTASK     = "supertask";
  const HASHLIST      = "hashlist";
  const SUPERHASHLIST = "superhashlist";
  const FILE          = "file";
  const CRACKER       = "cracker";
  const CONFIG        = "config";
  const USER          = "user";
  const GROUP         = "group";
  const ACCESS        = "access";
}

class USectionTest extends UApi {
  const CONNECTION = "connection";
  const ACCESS     = "access";
}

class USectionAgent extends UApi {
  const CREATE_VOUCHER = "createVoucher";
  const GET_BINARIES   = "getBinaries";
  const LIST_VOUCHERS  = "listVouchers";
  const DELETE_VOUCHER = "deleteVoucher";

  const LIST_AGENTS      = "listAgents";
  const GET              = "get";
  const SET_ACTIVE       = "setActive";
  const CHANGE_OWNER     = "changeOwner";
  const SET_NAME         = "setName";
  const SET_CPU_ONLY     = "setCpuOnly";
  const SET_EXTRA_PARAMS = "setExtraParams";
  const SET_ERROR_FLAG   = "setErrorFlag";
  const SET_TRUSTED      = "setTrusted";
}

class USectionTask extends UApi {
  const LIST_TASKS = "listTasks";
  const GET_TASK = "getTask";
  const LIST_SUBTASKS = "listSubtasks";
  const LIST_PRETASKS = "listPretasks";
  const GET_PRETASK = "getPretask";
  const LIST_SUPERTASKS = "listSupertasks";
  const GET_SUPERTASK = "getSupertask";
  const GET_CHUNK = "getChunk";

  const CREATE_TASK = "createTask";
  const RUN_PRETASK = "runPretask";
  const RUN_SUPERTASK = "runSupertask";
  const CREATE_PRETASK = "createPretask";
  const CREATE_SUPERTASK = "createSupertask";
  const IMPORT_SUPERTASK = "importSupertask";

  const SET_TASK_PRIORITY = "setTaskPriority";
  const SET_SUPERTASK_PRIORITY = "setSupertaskPriority";
  const SET_TASK_NAME = "setTaskName";
  const SET_TASK_COLOR = "setTaskColor";
  const SET_TASK_CPU_ONLY = "setTaskCpuOnly";
  const SET_TASK_SMALL = "setTaskSmall";
  const TASK_UNASSIGN_AGENT = "taskUnassignAgent";
  const DELETE_TASK = "deleteTask";
  const PURGE_TASK = "purgeTask";

  const SET_PRETASK_PRIORITY = "setPretaskPriority";
  const SET_PRETASK_NAME = "setPretaskName";
  const SET_PRETASK_COLOR = "setPretaskColor";
  const SET_PRETASK_CHUNKSIZE = "setPretaskChunksize";
  const SET_PRETASK_CPU_ONLY = "setPretaskCpuOnly";
  const SET_PRETASK_SMALL = "setPretaskSmall";
  const DELETE_PRETASK = "deletePretask";

  const SET_SUBTASK_PRIORITY = "setSubtaskPriority";
  const SET_SUPERTASK_NAME = "setSupertaskName";
  const DELETE_SUPERTASK = "deleteSupertask";
}

class USectionPretask extends UApi {}

class USectionSupertask extends UApi {}

class USectionHashlist extends UApi {}

class USectionSuperhashlist extends UApi {}

class USectionFile extends UApi {}

class USectionCracker extends UApi {}

class USectionConfig extends UApi {}

class USectionUser extends UApi {}

class USectionGroup extends UApi {}

class USectionAccess extends UApi {}