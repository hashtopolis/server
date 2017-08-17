<?php

/*
 * All define classes should start with 'D'
 */

// hashcat status numbers
class DHashcatStatus {
  const INIT                   = 0;
  const AUTOTUNE               = 1;
  const RUNNING                = 2;
  const PAUSED                 = 3;
  const EXHAUSTED              = 4;
  const CRACKED                = 5;
  const ABORTED                = 6;
  const QUIT                   = 7;
  const BYPASS                 = 8;
  const ABORTED_CHECKPOINT     = 9;
  const STATUS_ABORTED_RUNTIME = 10;
}

class DLimits {
  const PLAINTEXT_LENGTH = 200;
}

class DAccountAction {
  const SET_EMAIL       = "setEmail";
  const YUBIKEY_DISABLE = "yubikeyDisable";
  const YUBIKEY_ENABLE  = "yubikeyEnable";
  const SET_OTP1        = "setOTP1";
  const SET_OTP2        = "setOTP2";
  const SET_OTP3        = "setOTP3";
  const SET_OTP4        = "setOTP4";
  const UPDATE_LIFETIME = "updateLifetime";
  const CHANGE_PASSWORD = "changePassword";
}

class DAgentBinaryAction {
  const NEW_BINARY    = "newBinary";
  const EDIT_BINARY   = "editBinary";
  const DELETE_BINARY = "deleteBinary";
}

class DAgentAction {
  const CLEAR_ERRORS   = "clearErrors";
  const RENAME_AGENT   = "renameAgent";
  const SET_OWNER      = "setOwner";
  const SET_TRUSTED    = "setTrusted";
  const SET_IGNORE     = "setIgnore";
  const SET_PARAMETERS = "setParameters";
  const SET_ACTIVE     = "setActive";
  const DELETE_AGENT   = "deleteAgent";
  const ASSIGN_AGENT   = "assignAgent";
  const CREATE_VOUCHER = "createVoucher";
  const DELETE_VOUCHER = "deleteVoucher";
  const DOWNLOAD_AGENT = "downloadAgent";
  const SET_CPU        = "setCpu";
}

class DConfigAction {
  const UPDATE_CONFIG = "updateConfig";
  const REBUILD_CACHE = "rebuildCache";
  const RESCAN_FILES  = "rescanFiles";
  const CLEAR_ALL     = "clearAll";
}

class DFileAction {
  const DELETE_FILE = "deleteFile";
  const SET_SECRET  = "setSecret";
  const ADD_FILE    = "addFile";
  const EDIT_FILE   = "editFile";
}

class DHashcatAction {
  const DELETE_RELEASE = "deleteRelease";
  const CREATE_RELEASE = "createRelease";
}

class DHashlistAction {
  const APPLY_PRECONFIGURED_TASKS = "applyPreconfiguredTasks";
  const CREATE_WORDLIST           = "createWordlist";
  const SET_SECRET                = "setSecret";
  const RENAME_HASHLIST           = "renameHashlist";
  const PROCESS_ZAP               = "processZap";
  const EXPORT_HASHLIST           = "exportHashlist";
  const ZAP_HASHLIST              = "zapHashlist";
  const DELETE_HASHLIST           = "deleteHashlist";
  const CREATE_HASHLIST           = "createHashlist";
  const CREATE_SUPERHASHLIST      = "createSuperhashlist";
  const CREATE_LEFTLIST           = "createLeftlist";
}

class DHashtypeAction {
  const DELETE_HASHTYPE = "deleteHashtype";
  const ADD_HASHTYPE    = "addHashtype";
}

class DNotificationAction {
  const CREATE_NOTIFICATION = "createNotification";
  const SET_ACTIVE          = "setActive";
  const DELETE_NOTIFICATION = "deleteNotification";
}

class DSearchAction {
  const SEARCH = "search";
}

class DSupertaskAction {
  const DELETE_SUPERTASK = "deleteSupertask";
  const CREATE_SUPERTASK = "createSupertask";
  const APPLY_SUPERTASK  = "applySupertask";
  const IMPORT_SUPERTASK = "importSupertask";
}

class DTaskAction {
  const SET_BENCHMARK   = "setBenchmark";
  const SET_SMALL_TASK  = "setSmallTask";
  const SET_CPU_TASK    = "setCpuTask";
  const ABORT_CHUNK     = "abortChunk";
  const RESET_CHUNK     = "resetChunk";
  const PURGE_TASK      = "purgeTask";
  const SET_COLOR       = "setColor";
  const SET_TIME        = "setTime";
  const RENAME_TASK     = "renameTask";
  const DELETE_FINISHED = "deleteFinished";
  const DELETE_TASK     = "deleteTask";
  const SET_PRIORITY    = "setPriority";
  const CREATE_TASK     = "createTask";
}

class DUserAction {
  const DELETE_USER  = "deleteUser";
  const ENABLE_USER  = "enableUser";
  const DISABLE_USER = "disableUser";
  const SET_RIGHTS   = "setRights";
  const SET_PASSWORD = "setPassword";
  const CREATE_USER  = "createUser";
}

class DTaskTypes {
  const NORMAL    = 0;
  const SUPERTASK = 1;
  const SUBTASK   = 2;
}

class DStats {
  const AGENTS_ONLINE      = "agentsOnline";
  const AGENTS_ACTIVE      = "agentsActive";
  const AGENTS_TOTAL_SPEED = "agentsTotalSpeed";
  const TASKS_TOTAL        = "tasksTotal";
  const TASKS_FINISHED     = "tasksFinished";
  const TASKS_RUNNING      = "tasksRunning";
  const TASKS_QUEUED       = "tasksQueued";
}

// operating systems
class DOperatingSystem {
  const LINUX   = 0;
  const WINDOWS = 1;
  const OSX     = 2;
}

// hashlist formats
class DHashlistFormat {
  const PLAIN         = 0;
  const WPA           = 1;
  const BINARY        = 2;
  const SUPERHASHLIST = 3;
}

// access levels for user groups
class DAccessLevel { // if you change any of them here, you need to check if this is consistent with the database
  const VIEW_ONLY     = 1;
  const READ_ONLY     = 5;
  const USER          = 20;
  const SUPERUSER     = 30;
  const ADMINISTRATOR = 50;
}

// used config values
class DConfig {
  const BENCHMARK_TIME    = "benchtime";
  const CHUNK_DURATION    = "chunktime";
  const CHUNK_TIMEOUT     = "chunktimeout";
  const AGENT_TIMEOUT     = "agenttimeout";
  const HASHES_PAGE_SIZE  = "pagingSize";
  const FIELD_SEPARATOR   = "fieldseparator";
  const HASHLIST_ALIAS    = "hashlistAlias";
  const STATUS_TIMER      = "statustimer";
  const BLACKLIST_CHARS   = "blacklistChars";
  const NUMBER_LOGENTRIES = "numLogEntries";
  const TIME_FORMAT       = "timefmt";
  const BASE_URL          = "baseUrl";
  const DISP_TOLERANCE    = "disptolerance";
  const BATCH_SIZE        = "batchSize";
  const YUBIKEY_ID        = "yubikey_id";
  const YUBIKEY_KEY       = "yubikey_key";
  const YUBIKEY_URL       = "yubikey_url";
  const BASE_HOST         = "baseHost";
  const DONATE_OFF        = "donateOff";
  
  /**
   * Gives the format which a config input should have. Default is string if it's not a known config.
   * @param $config string
   * @return string
   */
  public static function getConfigType($config) {
    switch ($config) {
      case DConfig::BENCHMARK_TIME:
        return DConfigType::NUMBER_INPUT;
      case DConfig::CHUNK_DURATION:
        return DConfigType::NUMBER_INPUT;
      case DConfig::CHUNK_TIMEOUT:
        return DConfigType::NUMBER_INPUT;
      case DConfig::AGENT_TIMEOUT:
        return DConfigType::NUMBER_INPUT;
      case DConfig::HASHES_PAGE_SIZE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::FIELD_SEPARATOR:
        return DConfigType::STRING_INPUT;
      case DConfig::HASHLIST_ALIAS:
        return DConfigType::STRING_INPUT;
      case DConfig::STATUS_TIMER:
        return DConfigType::NUMBER_INPUT;
      case DConfig::BLACKLIST_CHARS:
        return DConfigType::STRING_INPUT;
      case DConfig::NUMBER_LOGENTRIES:
        return DConfigType::NUMBER_INPUT;
      case DConfig::TIME_FORMAT:
        return DConfigType::STRING_INPUT;
      case DConfig::BASE_URL:
        return DConfigType::STRING_INPUT;
      case Dconfig::DISP_TOLERANCE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::BATCH_SIZE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::BASE_HOST:
        return DConfigType::STRING_INPUT;
      case DConfig::DONATE_OFF:
        return DConfigType::NUMBER_INPUT;
    }
    return DConfigType::STRING_INPUT;
  }
  
  /**
   * @param $config string
   * @return string
   */
  public static function getConfigDescription($config) {
    switch ($config) {
      case DConfig::BENCHMARK_TIME:
        return "Time in seconds an agent should benchmark a task";
      case DConfig::CHUNK_DURATION:
        return "How long an agent approximately should need for completing one chunk";
      case DConfig::CHUNK_TIMEOUT:
        return "How long an agent must not respond until it is treated as timed out and the chunk will get shipped to other agents";
      case DConfig::AGENT_TIMEOUT:
        return "How long an agent must not respond until he is treated as not active anymore";
      case DConfig::HASHES_PAGE_SIZE:
        return "How many hashes are showed at once in the hashes view (Page size)";
      case DConfig::FIELD_SEPARATOR:
        return "What separator should be used to separate hash and plain (or salt)";
      case DConfig::HASHLIST_ALIAS:
        return "What string is used as hashlist alias when creating a task";
      case DConfig::STATUS_TIMER:
        return "After how many seconds the agent should send it's progress and cracks to the server";
      case DConfig::BLACKLIST_CHARS:
        return "Chars which are not allowed to be used in attack command inputs";
      case DConfig::NUMBER_LOGENTRIES:
        return "How many log entries should be saved. When this number is exceeded by 120%, the oldest ones will get deleted";
      case DConfig::TIME_FORMAT:
        return "Set the formatting of time displaying. Use syntax for PHPs date() method";
      case DConfig::BASE_URL:
        return "Base url for the webpage (this does not include hostname and is normally determined automatically on the installation)";
      case DConfig::DISP_TOLERANCE:
        return "How many percent a chunk can be longer than normal to finish a task (this avoids small chunks if the remaining part is slightly bigger than the normal chunk)";
      case DConfig::BATCH_SIZE:
        return "Batch size of SQL query when hashlist is sent to the agent";
      case DConfig::YUBIKEY_ID:
        return "Yubikey Client Id";
      case DConfig::YUBIKEY_KEY:
        return "Yubikey Secret Key";
      case DConfig::YUBIKEY_URL:
        return "Yubikey API Url";
      case DConfig::BASE_HOST:
        return "Base hostname/port/protocol to use. Only fill in to override the self-determined value.";
      case DConfig::DONATE_OFF:
        return "Hide donate information (insert '1' to hide)";
    }
    return $config;
  }
}

class DNotificationObjectType {
  const HASHLIST = "Hashlist";
  const AGENT    = "Agent";
  const USER     = "User";
  const TASK     = "Task";
  
  const NONE = "NONE";
}

class DNotificationType {
  const TASK_COMPLETE         = "taskComplete";
  const AGENT_ERROR           = "agentError";
  const OWN_AGENT_ERROR       = "ownAgentError"; //difference to AGENT_ERROR is that this can be configured by owners
  const LOG_ERROR             = "logError";
  const NEW_TASK              = "newTask";
  const NEW_HASHLIST          = "newHashlist";
  const HASHLIST_ALL_CRACKED  = "hashlistAllCracked";
  const HASHLIST_CRACKED_HASH = "hashlistCrackedHash";
  const USER_CREATED          = "userCreated";
  const USER_DELETED          = "userDeleted";
  const USER_LOGIN_FAILED     = "userLoginFailed";
  const LOG_WARN              = "logWarn";
  const LOG_FATAL             = "logFatal";
  const NEW_AGENT             = "newAgent";
  const DELETE_TASK           = "deleteTask";
  const DELETE_HASHLIST       = "deleteHashlist";
  const DELETE_AGENT          = "deleteAgent";
  
  public static function getAll() {
    return array(
      DNotificationType::TASK_COMPLETE,
      DNotificationType::AGENT_ERROR,
      DNotificationType::OWN_AGENT_ERROR,
      DNotificationType::LOG_ERROR,
      DNotificationType::NEW_TASK,
      DNotificationType::NEW_HASHLIST,
      DNotificationType::HASHLIST_ALL_CRACKED,
      DNotificationType::HASHLIST_CRACKED_HASH,
      DNotificationType::USER_CREATED,
      DNotificationType::USER_DELETED,
      DNotificationType::USER_LOGIN_FAILED,
      DNotificationType::LOG_WARN,
      DNotificationType::LOG_FATAL,
      DNotificationType::NEW_AGENT,
      DNotificationType::DELETE_TASK,
      DNotificationType::DELETE_HASHLIST,
      DNotificationType::DELETE_AGENT
    );
  }
  
  /**
   * @param $notificationType string
   * @return int access level
   */
  public static function getRequiredLevel($notificationType) {
    switch ($notificationType) {
      case DNotificationType::TASK_COMPLETE:
        return DAccessLevel::USER;
      case DNotificationType::AGENT_ERROR:
        return DAccessLevel::SUPERUSER;
      case DNotificationType::OWN_AGENT_ERROR:
        return DAccessLevel::USER;
      case DNotificationType::LOG_ERROR:
        return DAccessLevel::ADMINISTRATOR;
      case DNotificationType::NEW_TASK:
        return DAccessLevel::USER;
      case DNotificationType::NEW_HASHLIST:
        return DAccessLevel::USER;
      case DNotificationType::HASHLIST_ALL_CRACKED:
        return DAccessLevel::USER;
      case DNotificationType::HASHLIST_CRACKED_HASH:
        return DAccessLevel::USER;
      case DNotificationType::USER_CREATED:
        return DAccessLevel::ADMINISTRATOR;
      case DNotificationType::USER_DELETED:
        return DAccessLevel::ADMINISTRATOR;
      case DNotificationType::USER_LOGIN_FAILED:
        return DAccessLevel::ADMINISTRATOR;
      case DNotificationType::LOG_WARN:
        return DAccessLevel::ADMINISTRATOR;
      case DNotificationType::LOG_FATAL:
        return DAccessLevel::ADMINISTRATOR;
      case DNotificationType::NEW_AGENT:
        return DAccessLevel::SUPERUSER;
      case DNotificationType::DELETE_TASK:
        return DAccessLevel::USER;
      case DNotificationType::DELETE_HASHLIST:
        return DAccessLevel::USER;
      case DNotificationType::DELETE_AGENT:
        return DAccessLevel::SUPERUSER;
    }
    return DAccessLevel::ADMINISTRATOR;
  }
  
  public static function getObjectType($notificationType) {
    switch ($notificationType) {
      case DNotificationType::TASK_COMPLETE:
        return DNotificationObjectType::TASK;
      case DNotificationType::AGENT_ERROR:
        return DNotificationObjectType::AGENT;
      case DNotificationType::OWN_AGENT_ERROR:
        return DNotificationObjectType::AGENT;
      case DNotificationType::LOG_ERROR:
        return DNotificationObjectType::NONE;
      case DNotificationType::NEW_TASK:
        return DNotificationObjectType::NONE;
      case DNotificationType::NEW_HASHLIST:
        return DNotificationObjectType::NONE;
      case DNotificationType::HASHLIST_ALL_CRACKED:
        return DNotificationObjectType::HASHLIST;
      case DNotificationType::HASHLIST_CRACKED_HASH:
        return DNotificationObjectType::HASHLIST;
      case DNotificationType::USER_CREATED:
        return DNotificationObjectType::NONE;
      case DNotificationType::USER_DELETED:
        return DNotificationObjectType::USER;
      case DNotificationType::USER_LOGIN_FAILED:
        return DNotificationObjectType::USER;
      case DNotificationType::LOG_WARN:
        return DNotificationObjectType::NONE;
      case DNotificationType::LOG_FATAL:
        return DNotificationObjectType::NONE;
      case DNotificationType::NEW_AGENT:
        return DNotificationObjectType::NONE;
      case DNotificationType::DELETE_TASK:
        return DNotificationObjectType::TASK;
      case DNotificationType::DELETE_HASHLIST:
        return DNotificationObjectType::HASHLIST;
      case DNotificationType::DELETE_AGENT:
        return DNotificationObjectType::AGENT;
    }
    return DNotificationObjectType::NONE;
  }
}

class DPayloadKeys {
  const TASK        = "task";
  const AGENT       = "agent";
  const AGENT_ERROR = "agentError";
  const LOG_ENTRY   = "logEntry";
  const USER        = "user";
  const HASHLIST    = "hashlist";
  const NUM_CRACKED = "numCracked";
}

class DConfigType {
  const STRING_INPUT = "string";
  const NUMBER_INPUT = "number";
  const TICKBOX      = "checkbox";
}

// log entry types
class DLogEntry {
  const WARN  = "warning";
  const ERROR = "error";
  const FATAL = "fatal error";
  const INFO  = "information";
}

class DLogEntryIssuer {
  const API  = "API";
  const USER = "User";
}


