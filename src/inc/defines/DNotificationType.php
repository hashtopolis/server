<?php

namespace Hashtopolis\inc\defines;

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
    return match ($notificationType) {
      DNotificationType::TASK_COMPLETE, DNotificationType::NEW_TASK, DNotificationType::DELETE_TASK => DAccessControl::VIEW_TASK_ACCESS,
      DNotificationType::AGENT_ERROR, DNotificationType::OWN_AGENT_ERROR, DNotificationType::DELETE_AGENT => DAccessControl::VIEW_AGENT_ACCESS,
      DNotificationType::NEW_HASHLIST, DNotificationType::HASHLIST_ALL_CRACKED, DNotificationType::HASHLIST_CRACKED_HASH, DNotificationType::DELETE_HASHLIST => DAccessControl::VIEW_HASHLIST_ACCESS,
      DNotificationType::USER_CREATED, DNotificationType::USER_DELETED, DNotificationType::USER_LOGIN_FAILED => DAccessControl::USER_CONFIG_ACCESS,
      DNotificationType::NEW_AGENT => DAccessControl::MANAGE_AGENT_ACCESS,
      default => DAccessControl::SERVER_CONFIG_ACCESS,
    };
  }
  
  public static function getObjectType($notificationType) {
    return match ($notificationType) {
      DNotificationType::TASK_COMPLETE, DNotificationType::DELETE_TASK => DNotificationObjectType::TASK,
      DNotificationType::AGENT_ERROR, DNotificationType::OWN_AGENT_ERROR, DNotificationType::DELETE_AGENT => DNotificationObjectType::AGENT,
      DNotificationType::HASHLIST_ALL_CRACKED, DNotificationType::HASHLIST_CRACKED_HASH, DNotificationType::DELETE_HASHLIST => DNotificationObjectType::HASHLIST,
      DNotificationType::USER_DELETED, DNotificationType::USER_LOGIN_FAILED => DNotificationObjectType::USER,
      default => DNotificationObjectType::NONE,
    };
  }
}