<?php

// user api defines (these are started with 'U')

#####################
# Query definitions #
#####################

abstract class UQuery { // include only generalized query values
  const SECTION    = "section";
  const REQUEST    = "request";
  const ACCESS_KEY = "accessKey";
}

class UQueryAgent extends UQuery {
  const VOUCHER       = "voucher";
  const AGENT_ID      = "agentId";
  const ACTIVE        = "active";
  const USER          = "user";
  const NAME          = "name";
  const CPU_ONLY      = "cpuOnly";
  const EXTRA_PARAMS  = "extraParameters";
  const IGNORE_ERRORS = "ignoreErrors";
  const TRUSTED       = "trusted";
}

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
  
  const TASK_CRACKER_TYPE  = "crackerTypeId";
  const PRETASKS           = "pretasks";
  const MASKS              = "masks";
  const TASK_OPTIMIZED     = "optimizedFlag";
  const AGENT_ID           = "agentId";
  const SUPERTASK_PRIORITY = "supertaskPriority";
  const SUPERTASK_NAME     = "name";
  const TASK_BASEFILES     = "basefiles";
  const TASK_ITERFILES     = "iterfiles";
  
  const PRETASK_PRIORITY   = "priority";
  const PRETASK_MAX_AGENTS = "maxAgents";
  const PRETASK_NAME       = "name";
  const PRETASK_COLOR      = "color";
  const PRETASK_CHUNKSIZE  = "chunksize";
  const PRETASK_CPU_ONLY   = "isCpuOnly";
  const PRETASK_SMALL      = "isSmall";
}

class UQueryHashlist extends UQuery {
  const HASHLIST_ID = "hashlistId";
  
  const HASHLIST_NAME            = "name";
  const HASHLIST_IS_SALTED       = "isSalted";
  const HASHLIST_IS_SECRET       = "isSecret";
  const HASHLIST_HEX_SALTED      = "isHexSalt";
  const HASHLIST_SEPARATOR       = "separator";
  const HASHLIST_FORMAT          = "format";
  const HASHLIST_HASHTYPE_ID     = "hashtypeId";
  const HASHLIST_ACCESS_GROUP_ID = "accessGroupId";
  const HASHLIST_DATA            = "data";
  const HASHLIST_USE_BRAIN       = "useBrain";
  const HASHLIST_BRAIN_FEATURES  = "brainFeatures";
  const HASHLIST_IS_ARCHIVED     = "isArchived";
  
  const HASH = "hash";
}

class UQuerySuperhashlist extends UQuery {
  const SUPERHASHLIST_ID        = "superhashlistId";
  const SUPERHASHLIST_NAME      = "name";
  const SUPERHASHLIST_HASHLISTS = "hashlists";
}

class UQueryFile extends UQuery {
  const FILE_ID = "fileId";
  
  const FILENAME  = "filename";
  const FILE_TYPE = "fileType";
  const SOURCE    = "source";
  const DATA      = "data";
  
  const SET_SECRET      = "isSecret";
  const ACCESS_GROUP_ID = "accessGroupId";
}

class UQueryCracker extends UQuery {
  const CRACKER_ID         = "crackerTypeId";
  const CRACKER_VERSION_ID = "crackerVersionId";
  
  const CRACKER_NAME   = "crackerName";
  const BINARY_VERSION = "crackerBinaryVersion";
  const BINARY_NAME    = "crackerBinaryBasename";
  const BINARY_URL     = "crackerBinaryUrl";
}

class UQueryConfig extends UQuery {
  const CONFIG_ITEM  = "configItem";
  const CONFIG_VALUE = "value";
  const CONFIG_FORCE = "force";
}

class UQueryUser extends UQuery {
  const USER_ID = "userId";
  
  const USER_USERNAME  = "username";
  const USER_EMAIL     = "email";
  const RIGHT_GROUP_ID = "rightGroupId";
  
  const USER_PASSWORD       = "password";
  const USER_RIGHT_GROUP_ID = "rightGroupId";
}

class UQueryGroup extends UQuery {
  const GROUP_ID   = "groupId";
  const GROUP_NAME = "name";
  
  const AGENT_ID = "agentId";
  const USER_ID  = "userId";
}

class UQueryAccess extends UQuery {
  const RIGHT_GROUP_ID   = "rightGroupId";
  const RIGHT_GROUP_NAME = "name";
  const PERMISSIONS      = "permissions";
}

class UQueryAccount extends UQuery {
  const EMAIL          = "email";
  const SESSION_LENGTH = "sessionLength";
  
  const OLD_PASS = "oldPassword";
  const NEW_PASS = "newPassword";
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
  
  const AGENT_NAME            = "name";
  const AGENT_DEVICES         = "devices";
  const AGENT_OWNER           = "owner";
  const AGENT_OWNER_ID        = "userId";
  const AGENT_OWNER_NAME      = "username";
  const AGENT_CPU_ONLY        = "isCpuOnly";
  const AGENT_TRUSTED         = "isTrusted";
  const AGENT_ACTIVE          = "isActive";
  const AGENT_TOKEN           = "token";
  const AGENT_PARAMS          = "extraParameters";
  const AGENT_ERRORS          = "errorFlag";
  const AGENT_ACTIVITY        = "lastActivity";
  const AGENT_ACTIVITY_ACTION = "action";
  const AGENT_ACTIVITY_TIME   = "time";
  const AGENT_ACTIVITY_IP     = "ip";
}

class UResponseTask extends UResponse {
  const TASKS              = "tasks";
  const TASKS_ID           = "taskId";
  const TASKS_SUPERTASK_ID = "supertaskId";
  const TASKS_NAME         = "name";
  const TASKS_TYPE         = "type";
  const TASKS_HASHLIST     = "hashlistId";
  const TASKS_PRIORITY     = "priority";
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
  
  const PRETASKS          = "pretasks";
  const PRETASKS_ID       = "pretaskId";
  const PRETASKS_NAME     = "name";
  const PRETASKS_PRIORITY = "priority";
  
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
  
  const SUPERTASK_ID   = "supertaskId";
  const SUPERTASK_NAME = "name";
  
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

class UResponseHashlist extends UResponse {
  const HASHLISTS             = "hashlists";
  const HASHLISTS_ID          = "hashlistId";
  const HASHLISTS_NAME        = "name";
  const HASHLISTS_HASHTYPE_ID = "hashtypeId";
  const HASHLISTS_FORMAT      = "format";
  const HASHLISTS_COUNT       = "hashCount";
  
  const HASHLIST_ID             = "hashlistId";
  const HASHLIST_NAME           = "name";
  const HASHLIST_HASHTYPE_ID    = "hashtypeId";
  const HASHLIST_FORMAT         = "format";
  const HASHLIST_COUNT          = "hashCount";
  const HASHLIST_CRACKED        = "cracked";
  const HASHLIST_ACCESS_GROUP   = "accessGroupId";
  const HASHLIST_HEX_SALT       = "isHexSalt";
  const HASHLIST_SALTED         = "isSalted";
  const HASHLIST_SECRET         = "isSecret";
  const HASHLIST_SALT_SEPARATOR = "saltSeparator";
  const HASHLIST_NOTES          = "hashlistNotes";
  const HASHLIST_BRAIN          = "useBrain";
  const HASHLIST_IS_ARCHIVED    = "isArchived";
  
  const ZAP_LINES_PROCESSED = "linesProcessed";
  const ZAP_NEW_CRACKED     = "newCracked";
  const ZAP_ALREADY_CRACKED = "alreadyCracked";
  const ZAP_INVALID         = "invalidLines";
  const ZAP_NOT_FOUND       = "notFound";
  const ZAP_TIME_REQUIRED   = "processTime";
  const ZAP_TOO_LONG        = "tooLongPlains";
  
  const EXPORT_FILE_ID   = "fileId";
  const EXPORT_FILE_NAME = "filename";
  
  const HASH     = "hash";
  const PLAIN    = "plain";
  const CRACKPOS = "crackpos";
  const CRACKED  = "cracked";
}

class UResponseSuperhashlist extends UResponse {
  const SUPERHASHLISTS             = "superhashlists";
  const SUPERHASHLISTS_ID          = "hashlistId";
  const SUPERHASHLISTS_NAME        = "name";
  const SUPERHASHLISTS_HASHTYPE_ID = "hashtypeId";
  const SUPERHASHLISTS_COUNT       = "hashCount";
  
  const SUPERHASHLIST_ID           = "hashlistId";
  const SUPERHASHLIST_NAME         = "name";
  const SUPERHASHLIST_HASHTYPE_ID  = "hashtypeId";
  const SUPERHASHLIST_COUNT        = "hashCount";
  const SUPERHASHLIST_CRACKED      = "cracked";
  const SUPERHASHLIST_ACCESS_GROUP = "accessGroupId";
  const SUPERHASHLIST_SECRET       = "isSecret";
  const SUPERHASHLIST_HASHLISTS    = "hashlists";
}

class UResponseFile extends UResponse {
  const FILES          = "files";
  const FILES_FILE_ID  = "fileId";
  const FILES_FILETYPE = "fileType";
  const FILES_FILENAME = "filename";
  
  const FILE_ID       = "fileId";
  const FILE_TYPE     = "fileType";
  const FILE_FILENAME = "filename";
  const FILE_SECRET   = "isSecret";
  const FILE_SIZE     = "size";
  const FILE_URL      = "url";
}

class UResponseCracker extends UResponse {
  const CRACKERS      = "crackers";
  const CRACKERS_ID   = "crackerTypeId";
  const CRACKERS_NAME = "crackerTypeName";
  
  const CRACKER_ID   = "crackerTypeId";
  const CRACKER_NAME = "crackerTypeName";
  
  const VERSIONS             = "crackerVersions";
  const VERSIONS_ID          = "versionId";
  const VERSIONS_VERSION     = "version";
  const VERSIONS_URL         = "downloadUrl";
  const VERSIONS_BINARY_NAME = "binaryBasename";
}

class UResponseConfig extends UResponse {
  const CONFIG             = "items";
  const CONFIG_SECTION_ID  = "configSectionId";
  const CONFIG_ITEM        = "item";
  const CONFIG_VALUE       = "value";
  const CONFIG_TYPE        = "configType";
  const CONFIG_DESCRIPTION = "itemDescription";
  
  const SECTIONS      = "configSections";
  const SECTIONS_ID   = "configSectionId";
  const SECTIONS_NAME = "name";
}

class UResponseUser extends UResponse {
  const USERS          = "users";
  const USERS_ID       = "userId";
  const USERS_USERNAME = "username";
  
  const USER_ID               = "userId";
  const USER_USERNAME         = "username";
  const USER_EMAIL            = "email";
  const USER_RIGHT_GROUP_ID   = "rightGroupId";
  const USER_REGISTERED       = "registered";
  const USER_LAST_LOGIN       = "lastLogin";
  const USER_IS_VALID         = "isValid";
  const USER_SESSION_LIFETIME = "sessionLifetime";
}

class UResponseGroup extends UResponse {
  const GROUPS      = "groups";
  const GROUPS_ID   = "groupId";
  const GROUPS_NAME = "name";
  
  const GROUP_ID   = "groupId";
  const GROUP_NAME = "name";
  const USERS      = "users";
  const AGENTS     = "agents";
}

class UResponseAccess extends UResponse {
  const RIGHT_GROUPS_ID   = "rightGroupId";
  const RIGHT_GROUPS_NAME = "name";
  const RIGHT_GROUPS      = "rightGroups";
  
  const RIGHT_GROUP_ID   = "rightGroupId";
  const RIGHT_GROUP_NAME = "name";
  const PERMISSIONS      = "permissions";
  const MEMBERS          = "members";
  const WARNING          = "warning";
}

class UResponseAccount extends UResponse {
  const USER_ID        = "userId";
  const EMAIL          = "email";
  const RIGHT_GROUP_ID = "rightGroupId";
  const SESSION_LENGTH = "sessionLength";
}

###############################
# Section/Request definitions #
###############################

abstract class UApi {
  abstract function describe($constant);
  
  static function getConstants() {
    try {
      $oClass = new ReflectionClass(static::class);
    }
    catch (ReflectionException $e) {
      die("Exception: " . $e->getMessage());
    }
    return $oClass->getConstants();
  }
  
  static function getSection($section) {
    switch ($section) {
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
      case USection::ACCOUNT:
        return new USectionAccount();
    }
    return null;
  }
  
  static function getDescription($section, $constant) {
    $sectionObject = UApi::getSection($section);
    if ($sectionObject == null) {
      return "__" . $section . "_" . $constant . "__";
    }
    return $sectionObject->describe($constant);
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
  const ACCOUNT       = "account";
  
  public function describe($section) {
    // placeholder
    return $section;
  }
}

class USectionTest extends UApi {
  const CONNECTION = "connection";
  const ACCESS     = "access";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionTest::CONNECTION:
        return "Connection testing";
      case USectionTest::ACCESS:
        return "Verifying the API key and test if user has access to the API";
      default:
        return "__" . $constant . "__";
    }
  }
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
  const DELETE_AGENT     = "deleteAgent";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionAgent::CREATE_VOUCHER:
        return "Creating new vouchers";
      case USectionAgent::GET_BINARIES:
        return "Get a list of available agent binaries";
      case USectionAgent::LIST_VOUCHERS:
        return "List existing vouchers";
      case USectionAgent::DELETE_VOUCHER:
        return "Delete an existing voucher";
      case USectionAgent::LIST_AGENTS:
        return "List all agents";
      case USectionAgent::GET:
        return "Get details about an agent";
      case USectionAgent::SET_ACTIVE:
        return "Set an agent active/inactive";
      case USectionAgent::CHANGE_OWNER:
        return "Change the owner of an agent";
      case USectionAgent::SET_NAME:
        return "Set the name of an agent";
      case USectionAgent::SET_CPU_ONLY:
        return "Set if an agent is CPU only or not";
      case USectionAgent::SET_EXTRA_PARAMS:
        return "Set extra flags for an agent";
      case USectionAgent::SET_ERROR_FLAG:
        return "Set how errors from an agent should be handled";
      case USectionAgent::SET_TRUSTED:
        return "Set if an agent is trusted or not";
      case USectionAgent::DELETE_AGENT:
        return "Delete agents";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionTask extends UApi {
  const LIST_TASKS    = "listTasks";
  const GET_TASK      = "getTask";
  const LIST_SUBTASKS = "listSubtasks";
  const GET_CHUNK     = "getChunk";
  const GET_CRACKED   = "getCracked";
  
  const CREATE_TASK   = "createTask";
  const RUN_PRETASK   = "runPretask";
  const RUN_SUPERTASK = "runSupertask";
  
  const SET_TASK_PRIORITY          = "setTaskPriority";
  const SET_TASK_TOP_PRIORITY      = "setTaskTopPriority";
  const SET_SUPERTASK_PRIORITY     = "setSupertaskPriority";
  const SET_SUPERTASK_TOP_PRIORITY = "setSupertaskTopPriority";
  const SET_TASK_NAME              = "setTaskName";
  const SET_TASK_COLOR             = "setTaskColor";
  const SET_TASK_CPU_ONLY          = "setTaskCpuOnly";
  const SET_TASK_SMALL             = "setTaskSmall";
  const SET_TASK_MAX_AGENTS        = "setTaskMaxAgents";
  const TASK_UNASSIGN_AGENT        = "taskUnassignAgent";
  const TASK_ASSIGN_AGENT          = "taskAssignAgent";
  const DELETE_TASK                = "deleteTask";
  const PURGE_TASK                 = "purgeTask";
  
  const SET_SUPERTASK_NAME = "setSupertaskName";
  const DELETE_SUPERTASK   = "deleteSupertask";
  
  const ARCHIVE_TASK      = "archiveTask";
  const ARCHIVE_SUPERTASK = "archiveSupertask";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionTask::LIST_TASKS:
        return "List all tasks";
      case USectionTask::GET_TASK:
        return "Get details of a task";
      case USectionTask::LIST_SUBTASKS:
        return "List subtasks of a running supertask";
      case USectionTask::GET_CHUNK:
        return "Get details of a chunk";
      case USectionTask::CREATE_TASK:
        return "Create a new task";
      case USectionTask::RUN_PRETASK:
        return "Run an existing preconfigured task with a hashlist";
      case USectionTask::RUN_SUPERTASK:
        return "Run a configured supertask with a hashlist";
      case USectionTask::SET_TASK_PRIORITY:
        return "Set the priority of a task";
      case USectionTask::SET_TASK_TOP_PRIORITY:
        return "Set task priority to the previous highest plus one hundred";
      case USectionTask::SET_SUPERTASK_PRIORITY:
        return "Set the priority of a supertask";
      case USectionTask::SET_SUPERTASK_TOP_PRIORITY:
        return "Set supertask priority to the previous highest plus one hundred";
      case USectionTask::SET_TASK_NAME:
        return "Rename a task";
      case USectionTask::SET_TASK_COLOR:
        return "Set the color of a task";
      case USectionTask::SET_TASK_CPU_ONLY:
        return "Set if a task is CPU only or not";
      case USectionTask::SET_TASK_SMALL:
        return "Set if a task is small or not";
      case USectionTask::TASK_UNASSIGN_AGENT:
        return "Unassign an agent from a task";
      case USectionTask::DELETE_TASK:
        return "Delete a task";
      case USectionTask::PURGE_TASK:
        return "Purge a task";
      case USectionTask::SET_SUPERTASK_NAME:
        return "Set the name of a supertask";
      case USectionTask::DELETE_SUPERTASK:
        return "Delete a supertask";
      case USectionTask::ARCHIVE_TASK:
        return "Archive tasks";
      case USectionTask::ARCHIVE_SUPERTASK:
        return "Archive supertasks";
      case USectionTask::GET_CRACKED:
        return "Retrieve all cracked hashes by a task";
      case USectionTask::SET_TASK_MAX_AGENTS:
        return "Set max agents for tasks";
      case USectionTask::TASK_ASSIGN_AGENT:
        return "Assign agents to a task";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionPretask extends UApi {
  const LIST_PRETASKS  = "listPretasks";
  const GET_PRETASK    = "getPretask";
  const CREATE_PRETASK = "createPretask";
  
  const SET_PRETASK_PRIORITY   = "setPretaskPriority";
  const SET_PRETASK_MAX_AGENTS = "setPretaskMaxAgents";
  const SET_PRETASK_NAME       = "setPretaskName";
  const SET_PRETASK_COLOR      = "setPretaskColor";
  const SET_PRETASK_CHUNKSIZE  = "setPretaskChunksize";
  const SET_PRETASK_CPU_ONLY   = "setPretaskCpuOnly";
  const SET_PRETASK_SMALL      = "setPretaskSmall";
  const DELETE_PRETASK         = "deletePretask";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionPretask::LIST_PRETASKS:
        return "List all preconfigured tasks";
      case USectionPretask::GET_PRETASK:
        return "Get details about a preconfigured task";
      case USectionPretask::CREATE_PRETASK:
        return "Create preconfigured tasks";
      case USectionPretask::SET_PRETASK_PRIORITY:
        return "Set preconfigured tasks priorities";
      case USectionPretask::SET_PRETASK_NAME:
        return "Rename preconfigured tasks";
      case USectionPretask::SET_PRETASK_COLOR:
        return "Set the color of a preconfigured task";
      case USectionPretask::SET_PRETASK_CHUNKSIZE:
        return "Change the chunk size for a preconfigured task";
      case USectionPretask::SET_PRETASK_CPU_ONLY:
        return "Set if a preconfigured task is CPU only or not";
      case USectionPretask::SET_PRETASK_SMALL:
        return "Set if a preconfigured task is small or not";
      case USectionPretask::DELETE_PRETASK:
        return "Delete preconfigured tasks";
      case USectionPretask::SET_PRETASK_MAX_AGENTS:
        return "Set max agents for a preconfigured task";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionSupertask extends UApi {
  const LIST_SUPERTASKS    = "listSupertasks";
  const GET_SUPERTASK      = "getSupertask";
  const CREATE_SUPERTASK   = "createSupertask";
  const IMPORT_SUPERTASK   = "importSupertask";
  const SET_SUPERTASK_NAME = "setSupertaskName";
  const DELETE_SUPERTASK   = "deleteSupertask";
  const BULK_SUPERTASK     = "bulkSupertask";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionSupertask::LIST_SUPERTASKS:
        return "List all supertasks";
      case USectionSupertask::GET_SUPERTASK:
        return "Get details of a supertask";
      case USectionSupertask::CREATE_SUPERTASK:
        return "Create a supertask";
      case USectionSupertask::IMPORT_SUPERTASK:
        return "Import a supertask from masks";
      case USectionSupertask::SET_SUPERTASK_NAME:
        return "Rename a configured supertask";
      case USectionSupertask::DELETE_SUPERTASK:
        return "Delete a supertask";
      case USectionSupertask::BULK_SUPERTASK:
        return "Create supertask out base command with files";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionHashlist extends UApi {
  const LIST_HASLISTS     = "listHashlists";
  const GET_HASHLIST      = "getHashlist";
  const CREATE_HASHLIST   = "createHashlist";
  const SET_HASHLIST_NAME = "setHashlistName";
  const SET_SECRET        = "setSecret";
  const SET_ARCHIVED      = "setArchived";
  
  const IMPORT_CRACKED    = "importCracked";
  const EXPORT_CRACKED    = "exportCracked";
  const GENERATE_WORDLIST = "generateWordlist";
  const EXPORT_LEFT       = "exportLeft";
  
  const DELETE_HASHLIST  = "deleteHashlist";
  const GET_HASH         = "getHash";
  const GET_CRACKED      = "getCracked";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionHashlist::LIST_HASLISTS:
        return "List all hashlists";
      case USectionHashlist::GET_HASHLIST:
        return "Get details of a hashlist";
      case USectionHashlist::CREATE_HASHLIST:
        return "Create a new hashlist";
      case USectionHashlist::SET_HASHLIST_NAME:
        return "Rename hashlists";
      case USectionHashlist::SET_SECRET:
        return "Set if a hashlist is secret or not";
      case USectionHashlist::IMPORT_CRACKED:
        return "Import cracked hashes";
      case USectionHashlist::EXPORT_CRACKED:
        return "Export cracked hashes";
      case USectionHashlist::GENERATE_WORDLIST:
        return "Generate wordlist from founds";
      case USectionHashlist::EXPORT_LEFT:
        return "Export a left list of uncracked hashes";
      case USectionHashlist::DELETE_HASHLIST:
        return "Delete hashlists";
      case USectionHashlist::GET_HASH:
        return "Query for specific hashes";
      case USectionHashlist::GET_CRACKED:
        return "Query cracked hashes of a hashlist";
      case USectionHashlist::SET_ARCHIVED:
        return "Query to archive/un-archie hashlist";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionSuperhashlist extends UApi {
  const LIST_SUPERHASHLISTS  = "listSuperhashlists";
  const GET_SUPERHASHLIST    = "getSuperhashlist";
  const CREATE_SUPERHASHLIST = "createSuperhashlist";
  const DELETE_SUPERHASHLIST = "deleteSuperhashlist";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionSuperhashlist::LIST_SUPERHASHLISTS:
        return "List all superhashlists";
      case USectionSuperhashlist::GET_SUPERHASHLIST:
        return "Get details about a superhashlist";
      case USectionSuperhashlist::CREATE_SUPERHASHLIST:
        return "Create superhashlists";
      case USectionSuperhashlist::DELETE_SUPERHASHLIST:
        return "Delete superhashlists";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionFile extends UApi {
  const LIST_FILES = "listFiles";
  const GET_FILE   = "getFile";
  const ADD_FILE   = "addFile";
  
  const RENAME_FILE   = "renameFile";
  const SET_SECRET    = "setSecret";
  const DELETE_FILE   = "deleteFile";
  const SET_FILE_TYPE = "setFileType";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionFile::LIST_FILES:
        return "List all files";
      case USectionFile::GET_FILE:
        return "Get details of a file";
      case USectionFile::ADD_FILE:
        return "Add new files";
      case USectionFile::RENAME_FILE:
        return "Rename files";
      case USectionFile::SET_SECRET:
        return "Set if a file is secret or not";
      case USectionFile::DELETE_FILE:
        return "Delete files";
      case USectionFile::SET_FILE_TYPE:
        return "Change type of files";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionCracker extends UApi {
  const LIST_CRACKERS  = "listCrackers";
  const GET_CRACKER    = "getCracker";
  const DELETE_VERSION = "deleteVersion";
  const DELETE_CRACKER = "deleteCracker";
  
  const CREATE_CRACKER = "createCracker";
  const ADD_VERSION    = "addVersion";
  const UPDATE_VERSION = "updateVersion";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionCracker::LIST_CRACKERS:
        return "List all crackers";
      case USectionCracker::GET_CRACKER:
        return "Get details of a cracker";
      case USectionCracker::DELETE_VERSION:
        return "Delete a specific version of a cracker";
      case USectionCracker::DELETE_CRACKER:
        return "Deleting crackers";
      case USectionCracker::CREATE_CRACKER:
        return "Create new crackers";
      case USectionCracker::ADD_VERSION:
        return "Add new cracker versions";
      case USectionCracker::UPDATE_VERSION:
        return "Update cracker versions";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionConfig extends UApi {
  const LIST_SECTIONS = "listSections";
  const LIST_CONFIG   = "listConfig";
  const GET_CONFIG    = "getConfig";
  const SET_CONFIG    = "setConfig";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionConfig::LIST_SECTIONS:
        return "List available sections in config";
      case USectionConfig::LIST_CONFIG:
        return "List config options of a given section";
      case USectionConfig::GET_CONFIG:
        return "Get current value of a config";
      case USectionConfig::SET_CONFIG:
        return "Change values of configs";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionUser extends UApi {
  const LIST_USERS           = "listUsers";
  const GET_USER             = "getUser";
  const CREATE_USER          = "createUser";
  const DISABLE_USER         = "disableUser";
  const ENABLE_USER          = "enableUser";
  const DISABLE_LDAP         = "disableLDAP";
  const ENABLE_LDAP          = "enableLDAP";
  const SET_USER_PASSWORD    = "setUserPassword";
  const SET_USER_RIGHT_GROUP = "setUserRightGroup";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionUser::LIST_USERS:
        return "List all users";
      case USectionUser::GET_USER:
        return "Get details of a user";
      case USectionUser::CREATE_USER:
        return "Create new users";
      case USectionUser::DISABLE_USER:
        return "Disable a user account";
      case USectionUser::ENABLE_USER:
        return "Enable a user account";
      case USectionUser::DISABLE_LDAP:
        return "Disable LDAP auth";
      case USectionUser::ENABLE_LDAP:
        return "Enable LDAP auth";
      case USectionUser::SET_USER_PASSWORD:
        return "Set a user's password";
      case USectionUser::SET_USER_RIGHT_GROUP:
        return "Change the permission group for a user";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionGroup extends UApi {
  const LIST_GROUPS        = "listGroups";
  const GET_GROUP          = "getGroup";
  const CREATE_GROUP       = "createGroup";
  const ABORT_CHUNKS_GROUP = "abortChunksGroup";
  const DELETE_GROUP       = "deleteGroup";
  
  const ADD_AGENT    = "addAgent";
  const ADD_USER     = "addUser";
  const REMOVE_AGENT = "removeAgent";
  const REMOVE_USER  = "removeUser";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionGroup::LIST_GROUPS:
        return "List all groups";
      case USectionGroup::GET_GROUP:
        return "Get details of a group";
      case USectionGroup::CREATE_GROUP:
        return "Create new groups";
      case USectionGroup::ABORT_CHUNKS_GROUP:
        return "Abort all chunks dispatched to agents of this group";
      case USectionGroup::DELETE_GROUP:
        return "Delete groups";
      case USectionGroup::ADD_AGENT:
        return "Add agents to groups";
      case USectionGroup::ADD_USER:
        return "Add users to groups";
      case USectionGroup::REMOVE_AGENT:
        return "Remove agents from groups";
      case USectionGroup::REMOVE_USER:
        return "Remove users from groups";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionAccess extends UApi {
  const LIST_GROUPS     = "listGroups";
  const GET_GROUP       = "getGroup";
  const CREATE_GROUP    = "createGroup";
  const DELETE_GROUP    = "deleteGroup";
  const SET_PERMISSIONS = "setPermissions";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionAccess::LIST_GROUPS:
        return "List permission groups";
      case USectionAccess::GET_GROUP:
        return "Get details of a permission group";
      case USectionAccess::CREATE_GROUP:
        return "Create a new permission group";
      case USectionAccess::DELETE_GROUP:
        return "Delete permission groups";
      case USectionAccess::SET_PERMISSIONS:
        return "Update permissions of a group";
      default:
        return "__" . $constant . "__";
    }
  }
}

class USectionAccount extends UApi {
  const GET_INFORMATION    = "getInformation";
  const SET_EMAIL          = "setEmail";
  const SET_SESSION_LENGTH = "setSessionLength";
  const CHANGE_PASSWORD    = "changePassword";
  
  public function describe($constant) {
    switch ($constant) {
      case USectionAccount::GET_INFORMATION:
        return "Get account information";
      case USectionAccount::SET_EMAIL:
        return "Change email";
      case USectionAccount::SET_SESSION_LENGTH:
        return "Update session length";
      case USectionAccount::CHANGE_PASSWORD:
        return "Change password";
      default:
        return "__" . $constant . "__";
    }
  }
}
