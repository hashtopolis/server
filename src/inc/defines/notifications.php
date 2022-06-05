<?php

class DNotificationAction {
  const CREATE_NOTIFICATION      = "createNotification";
  const CREATE_NOTIFICATION_PERM = DAccessControl::LOGIN_ACCESS;
  
  const SET_ACTIVE      = "setActive";
  const SET_ACTIVE_PERM = DAccessControl::LOGIN_ACCESS;
  
  const DELETE_NOTIFICATION      = "deleteNotification";
  const DELETE_NOTIFICATION_PERM = DAccessControl::LOGIN_ACCESS;
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
   * @return string|array permission required
   */
  public static function getRequiredPermission($notificationType) {
    switch ($notificationType) {
      case DNotificationType::TASK_COMPLETE:
        return DAccessControl::VIEW_TASK_ACCESS;
      case DNotificationType::AGENT_ERROR:
        return DAccessControl::VIEW_AGENT_ACCESS;
      case DNotificationType::OWN_AGENT_ERROR:
        return DAccessControl::VIEW_AGENT_ACCESS;
      case DNotificationType::LOG_ERROR:
        return DAccessControl::SERVER_CONFIG_ACCESS;
      case DNotificationType::NEW_TASK:
        return DAccessControl::VIEW_TASK_ACCESS;
      case DNotificationType::NEW_HASHLIST:
        return DAccessControl::VIEW_HASHLIST_ACCESS;
      case DNotificationType::HASHLIST_ALL_CRACKED:
        return DAccessControl::VIEW_HASHLIST_ACCESS;
      case DNotificationType::HASHLIST_CRACKED_HASH:
        return DAccessControl::VIEW_HASHLIST_ACCESS;
      case DNotificationType::USER_CREATED:
        return DAccessControl::USER_CONFIG_ACCESS;
      case DNotificationType::USER_DELETED:
        return DAccessControl::USER_CONFIG_ACCESS;
      case DNotificationType::USER_LOGIN_FAILED:
        return DAccessControl::USER_CONFIG_ACCESS;
      case DNotificationType::LOG_WARN:
        return DAccessControl::SERVER_CONFIG_ACCESS;
      case DNotificationType::LOG_FATAL:
        return DAccessControl::SERVER_CONFIG_ACCESS;
      case DNotificationType::NEW_AGENT:
        return DAccessControl::MANAGE_AGENT_ACCESS;
      case DNotificationType::DELETE_TASK:
        return DAccessControl::VIEW_TASK_ACCESS;
      case DNotificationType::DELETE_HASHLIST:
        return DAccessControl::VIEW_HASHLIST_ACCESS;
      case DNotificationType::DELETE_AGENT:
        return DAccessControl::VIEW_AGENT_ACCESS;
    }
    return DAccessControl::SERVER_CONFIG_ACCESS;
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