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

class DFileAction {
  const DELETE_FILE = "deleteFile";
  const SET_SECRET  = "setSecret";
  const ADD_FILE    = "addFile";
  const EDIT_FILE   = "editFile";
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


