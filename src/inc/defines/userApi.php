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
  const SUPERTASK_ID = "supertaskId";
  const PRETASK_ID = "pretaskId";
  const CHUNK_ID = "chunkId";

  const TASK_NAME = "name";
  const TASK_HASHLIST = "hashlistId";
  const TASK_ATTACKCMD = "attackCmd";
  const TASK_CHUNKSIZE = "chunksize";
  const TASK_STATUS = "statusTimer";
  const TASK_BENCHTYPE = "benchmarkType";
  const TASK_COLOR = "color";
  const TASK_CPU_ONLY = "isCpuOnly";
  const TASK_SMALL = "isSmall";
  const TASK_SKIP = "skip";
  const TASK_CRACKER_VERSION = "crackerVersionId";
  const TASK_FILES = "files";
  const TASK_PRIORITY = "priority";

  const TASK_CRACKER_TYPE = "crackerTypeId";
  const PRETASKS = "pretasks";
  const MASKS = "masks";
  const TASK_OPTIMIZED = "optimizedFlag";
  const AGENT_ID = "agentId";
  const SUPERTASK_PRIORITY = "supertaskPriority";
  const SUPERTASK_NAME = "name";

  const PRETASK_PRIORITY = "priority";
  const PRETASK_NAME = "name";
  const PRETASK_COLOR = "color";
  const PRETASK_CHUNKSIZE = "chunksize";
  const PRETASK_CPU_ONLY = "isCpuOnly";
  const PRETASK_SMALL = "isSmall";
}

class UQueryHashlist extends UQuery {
  const HASHLIST_ID = "hashlistId";

  const HASHLIST_NAME = "name";
  const HASHLIST_IS_SALTED = "isSalted";
  const HASHLIST_IS_SECRET = "isSecret";
  const HASHLIST_HEX_SALTED = "isHexSalt";
  const HASHLIST_SEPARATOR = "separator";
  const HASHLIST_FORMAT = "format";
  const HASHLIST_HASHTYPE_ID = "hashtypeId";
  const HASHLIST_ACCESS_GROUP_ID = "accessGroupId";
  const HASHLIST_DATA = "data";
}

class UQuerySuperhashlist extends UQuery{
  const SUPERHASHLIST_ID = "superhashlistId";
  const SUPERHASHLIST_NAME = "name";
  const SUPERHASHLIST_HASHLISTS = "hashlists";
}

class UQueryFile extends UQuery {
  const FILE_ID = "fileId";

  const FILENAME = "filename";
  const FILE_TYPE = "fileType";
  const SOURCE = "source";
  const DATA = "data";

  const SET_SECRET = "isSecret";
}

class UQueryCracker extends UQuery {
  const CRACKER_ID = "crackerTypeId";
  const CRACKER_VERSION_ID = "crackerVersionId";

  const CRACKER_NAME = "crackerName";
  const BINARY_VERSION = "crackerBinaryVersion";
  const BINARY_NAME = "crackerBinaryBasename";
  const BINARY_URL = "crackerBinaryUrl";
}

class UQueryConfig extends UQuery {
  const CONFIG_ITEM = "configItem";
  const CONFIG_VALUE = "value";
  const CONFIG_FORCE = "force";
}

class UQueryUser extends UQuery {
  const USER_ID = "userId";

  const USER_USERNAME = "username";
  const USER_EMAIL = "email";
  const RIGHT_GROUP_ID = "rightGroupId";

  const USER_PASSWORD = "password";
  const USER_RIGHT_GROUP_ID = "rightGroupId";
}

class UQueryGroup extends UQuery {
  const GROUP_ID = "groupId";
  const GROUP_NAME = "name";

  const AGENT_ID = "agentId";
  const USER_ID = "userId";
}

class UQueryAccess extends UQuery {
  const RIGHT_GROUP_ID = "rightGroupId";
  const RIGHT_GROUP_NAME = "name";
  const PERMISSIONS = "permissions";
}

class UQueryAccount extends UQuery {
  const EMAIL = "email";
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

  const SUBTASKS = "subtasks";

  const PRETASKS = "pretasks";
  const PRETASKS_ID = "pretaskId";
  const PRETASKS_NAME = "name";
  const PRETASKS_PRIORITY = "priority";

  const PRETASK_ID = "pretaskId";
  const PRETASK_NAME = "name";
  const PRETASK_ATTACK = "attackCmd";
  const PRETASK_CHUNKSIZE = "chunksize";
  const PRETASK_COLOR = "color";
  const PRETASK_BENCH_TYPE = "benchmarkType";
  const PRETASK_STATUS = "statusTimer";
  const PRETASK_PRIORITY = "priority";
  const PRETASK_CPU_ONLY = "isCpuOnly";
  const PRETASK_SMALL = "isSmall";
  const PRETASK_FILES = "files";
  const PRETASK_FILES_ID = "fileId";
  const PRETASK_FILES_NAME = "filename";
  const PRETASK_FILES_SIZE = "size";

  const SUPERTASKS = "supertasks";
  const SUPERTASKS_ID = "supertaskId";
  const SUPERTASKS_NAME = "name";

  const SUPERTASK_ID = "supertaskId";
  const SUPERTASK_NAME = "name";

  const CHUNK_ID = "chunkId";
  const CHUNK_START = "start";
  const CHUNK_LENGTH = "length";
  const CHUNK_CHECKPOINT = "checkpoint";
  const CHUNK_PROGRESS = "progress";
  const CHUNK_TASK = "taskId";
  const CHUNK_AGENT = "agentId";
  const CHUNK_DISPATCHED = "dispatchTime";
  const CHUNK_ACTIVITY = "lastActivity";
  const CHUNK_STATE = "state";
  const CHUNK_CRACKED = "cracked";
  const CHUNK_SPEED = "speed";
}

class UResponseHashlist extends UResponse {
  const HASHLISTS = "hashlists";
  const HASHLISTS_ID = "hashlistId";
  const HASHLISTS_NAME = "name";
  const HASHLISTS_HASHTYPE_ID = "hashtypeId";
  const HASHLISTS_FORMAT = "format";
  const HASHLISTS_COUNT = "hashCount";

  const HASHLIST_ID = "hashlistId";
  const HASHLIST_NAME = "name";
  const HASHLIST_HASHTYPE_ID = "hashtypeId";
  const HASHLIST_FORMAT = "format";
  const HASHLIST_COUNT = "hashCount";
  const HASHLIST_CRACKED = "cracked";
  const HASHLIST_ACCESS_GROUP = "accessGroupId";
  const HASHLIST_HEX_SALT = "isHexSalt";
  const HASHLIST_SALTED = "isSalted";
  const HASHLIST_SECRET = "isSecret";
  const HASHLIST_SALT_SEPARATOR = "saltSeparator";

  const ZAP_LINES_PROCESSED = "linesProcessed";
  const ZAP_NEW_CRACKED = "newCracked";
  const ZAP_ALREADY_CRACKED = "alreadyCracked";
  const ZAP_INVALID = "invalidLines";
  const ZAP_NOT_FOUND = "notFound";
  const ZAP_TIME_REQUIRED = "processTime";
  const ZAP_TOO_LONG = "tooLongPlains";

  const EXPORT_FILE_ID = "fileId";
  const EXPORT_FILE_NAME = "filename";
}

class UResponseSuperhashlist extends UResponse {
  const SUPERHASHLISTS = "superhashlists";
  const SUPERHASHLISTS_ID = "hashlistId";
  const SUPERHASHLISTS_NAME = "name";
  const SUPERHASHLISTS_HASHTYPE_ID = "hashtypeId";
  const SUPERHASHLISTS_COUNT = "hashCount";

  const SUPERHASHLIST_ID = "hashlistId";
  const SUPERHASHLIST_NAME = "name";
  const SUPERHASHLIST_HASHTYPE_ID = "hashtypeId";
  const SUPERHASHLIST_COUNT = "hashCount";
  const SUPERHASHLIST_CRACKED = "cracked";
  const SUPERHASHLIST_ACCESS_GROUP = "accessGroupId";
  const SUPERHASHLIST_SECRET = "isSecret";
  const SUPERHASHLIST_HASHLISTS = "hashlists";
}

class UResponseFile extends UResponse {
  const FILES = "files";
  const FILES_FILE_ID = "fileId";
  const FILES_FILETYPE = "fileType";
  const FILES_FILENAME = "filename";

  const FILE_ID = "fileId";
  const FILE_TYPE = "fileType";
  const FILE_FILENAME = "filename";
  const FILE_SECRET = "isSecret";
  const FILE_SIZE = "size";
  const FILE_URL = "url";
}

class UResponseCracker extends UResponse {
  const CRACKERS = "crackers";
  const CRACKERS_ID = "crackerTypeId";
  const CRACKERS_NAME = "crackerTypeName";

  const CRACKER_ID = "crackerTypeId";
  const CRACKER_NAME = "crackerTypeName";

  const VERSIONS = "crackerVersions";
  const VERSIONS_ID = "versionId";
  const VERSIONS_VERSION = "version";
  const VERSIONS_URL = "downloadUrl";
  const VERSIONS_BINARY_NAME = "binaryBasename";
}

class UResponseConfig extends UResponse {
  const CONFIG = "items";
  const CONFIG_SECTION_ID = "configSectionId";
  const CONFIG_ITEM = "item";
  const CONFIG_VALUE = "value";
  const CONFIG_TYPE = "configType";
  const CONFIG_DESCRIPTION = "itemDescription";

  const SECTIONS = "configSections";
  const SECTIONS_ID = "configSectionId";
  const SECTIONS_NAME = "name";
}

class UResponseUser extends UResponse {
  const USERS = "users";
  const USERS_ID = "userId";
  const USERS_USERNAME = "username";

  const USER_ID = "userId";
  const USER_USERNAME = "username";
  const USER_EMAIL = "email";
  const USER_RIGHT_GROUP_ID = "rightGroupId";
  const USER_REGISTERED = "registered";
  const USER_LAST_LOGIN = "lastLogin";
  const USER_IS_VALID = "isValid";
  const USER_SESSION_LIFETIME = "sessionLifetime";
}

class UResponseGroup extends UResponse {
  const GROUPS = "groups";
  const GROUPS_ID = "groupId";
  const GROUPS_NAME = "name";

  const GROUP_ID = "groupId";
  const GROUP_NAME = "name";
  const USERS = "users";
  const AGENTS = "agents";
}

class UResponseAccess extends UResponse {
  const RIGHT_GROUPS_ID = "rightGroupId";
  const RIGHT_GROUPS_NAME = "name";
  const RIGHT_GROUPS = "rightGroups";

  const RIGHT_GROUP_ID = "rightGroupId";
  const RIGHT_GROUP_NAME = "name";
  const PERMISSIONS = "permissions";
  const MEMBERS = "members";
  const WARNING = "warning";
}

class UResponseAccount extends UResponse {
  const USER_ID = "userId";
  const EMAIL = "email";
  const RIGHT_GROUP_ID = "rightGroupId";
  const SESSION_LENGTH = "sessionLength";
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
  const ACCOUNT       = "account";
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
  const GET_CHUNK = "getChunk";

  const CREATE_TASK = "createTask";
  const RUN_PRETASK = "runPretask";
  const RUN_SUPERTASK = "runSupertask";

  const SET_TASK_PRIORITY = "setTaskPriority";
  const SET_SUPERTASK_PRIORITY = "setSupertaskPriority";
  const SET_TASK_NAME = "setTaskName";
  const SET_TASK_COLOR = "setTaskColor";
  const SET_TASK_CPU_ONLY = "setTaskCpuOnly";
  const SET_TASK_SMALL = "setTaskSmall";
  const TASK_UNASSIGN_AGENT = "taskUnassignAgent";
  const DELETE_TASK = "deleteTask";
  const PURGE_TASK = "purgeTask";

  const SET_SUPERTASK_NAME = "setSupertaskName";
  const DELETE_SUPERTASK = "deleteSupertask";
}

class USectionPretask extends UApi {
  const LIST_PRETASKS = "listPretasks";
  const GET_PRETASK = "getPretask";
  const CREATE_PRETASK = "createPretask";

  const SET_PRETASK_PRIORITY = "setPretaskPriority";
  const SET_PRETASK_NAME = "setPretaskName";
  const SET_PRETASK_COLOR = "setPretaskColor";
  const SET_PRETASK_CHUNKSIZE = "setPretaskChunksize";
  const SET_PRETASK_CPU_ONLY = "setPretaskCpuOnly";
  const SET_PRETASK_SMALL = "setPretaskSmall";
  const DELETE_PRETASK = "deletePretask";
}

class USectionSupertask extends UApi {
  const LIST_SUPERTASKS = "listSupertasks";
  const GET_SUPERTASK = "getSupertask";
  const CREATE_SUPERTASK = "createSupertask";
  const IMPORT_SUPERTASK = "importSupertask";
  const SET_SUPERTASK_NAME = "setSupertaskName";
  const DELETE_SUPERTASK = "deleteSupertask";
}

class USectionHashlist extends UApi {
  const LIST_HASLISTS = "listHashlists";
  const GET_HASHLIST = "getHashlist";
  const CREATE_HASHLIST = "createHashlist";
  const SET_HASHLIST_NAME = "setHashlistName";
  const SET_SECRET = "setSecret";

  const IMPORT_CRACKED = "importCracked";
  const EXPORT_CRACKED = "exportCracked";
  const GENERATE_WORDLIST = "generateWordlist";
  const EXPORT_LEFT = "exportLeft";

  const DELETE_HASHLIST = "deleteHashlist";
}

class USectionSuperhashlist extends UApi {
  const LIST_SUPERHASHLISTS = "listSuperhashlists";
  const GET_SUPERHASHLIST = "getSuperhashlist";
  const CREATE_SUPERHASHLIST = "createSuperhashlist";
  const DELETE_SUPERHASHLIST = "deleteSuperhashlist";
}

class USectionFile extends UApi {
  const LIST_FILES = "listFiles";
  const GET_FILE = "getFile";
  const ADD_FILE = "addFile";

  const RENAME_FILE = "renameFile";
  const SET_SECRET = "setSecret";
  const DELETE_FILE = "deleteFile";
  const SET_FILE_TYPE = "setFileType";
}

class USectionCracker extends UApi {
  const LIST_CRACKERS = "listCrackers";
  const GET_CRACKER = "getCracker";
  const DELETE_VERSION = "deleteVersion";
  const DELETE_CRACKER = "deleteCracker";

  const CREATE_CRACKER = "createCracker";
  const ADD_VERSION = "addVersion";
  const UPDATE_VERSION = "updateVersion";
}

class USectionConfig extends UApi {
  const LIST_SECTIONS = "listSections";
  const LIST_CONFIG = "listConfig";
  const GET_CONFIG = "getConfig";
  const SET_CONFIG = "setConfig";
}

class USectionUser extends UApi {
  const LIST_USERS = "listUsers";
  const GET_USER = "getUser";
  const CREATE_USER = "createUser";
  const DISABLE_USER = "disableUser";
  const ENABLE_USER = "enableUser";
  const SET_USER_PASSWORD = "setUserPassword";
  const SET_USER_RIGHT_GROUP = "setUserRightGroup";
}

class USectionGroup extends UApi {
  const LIST_GROUPS = "listGroups";
  const GET_GROUP = "getGroup";
  const CREATE_GROUP = "createGroup";
  const DELETE_GROUP = "deleteGroup";

  const ADD_AGENT = "addAgent";
  const ADD_USER = "addUser";
  const REMOVE_AGENT = "removeAgent";
  const REMOVE_USER = "removeUser";
}

class USectionAccess extends UApi {
  const LIST_GROUPS = "listGroups";
  const GET_GROUP = "getGroup";
  const CREATE_GROUP = "createGroup";
  const DELETE_GROUP = "deleteGroup";
  const SET_PERMISSIONS = "setPermissions";
}

class USectionAccount extends UApi {
  const GET_INFORMATION = "getInformation";
  const SET_EMAIL = "setEmail";
  const SET_SESSION_LENGTH = "setSessionLength";
  const CHANGE_PASSWORD = "changePassword";
}