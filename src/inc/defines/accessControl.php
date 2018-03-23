<?php

class DAccessControl {
  const VIEW_HASHLIST_ACCESS        = "viewHashlistAccess";
  const MANAGE_HASHLIST_ACCESS      = "manageHashlistAccess";
  const CREATE_HASHLIST_ACCESS      = "createHashlistAccess";
  const CREATE_SUPERHASHLIST_ACCESS = "createSuperhashlistAccess";
  const VIEW_AGENT_ACCESS           = array("viewAgentsAccess", DAccessControl::MANAGE_AGENT_ACCESS, DAccessControl::CREATE_AGENT_ACCESS);
  const MANAGE_AGENT_ACCESS         = "manageAgentAccess";
  const CREATE_AGENT_ACCESS         = "createAgentAccess";
  const VIEW_TASK_ACCESS            = array("viewTaskAccess", DAccessControl::CREATE_TASK_ACCESS, DAccessControl::CREATE_PRETASK_ACCESS, DAccessControl::CREATE_SUPERTASK_ACCESS);
  const RUN_TASK_ACCESS             = "runTaskAccess";
  const CREATE_TASK_ACCESS          = "createTaskAccess";
  const CREATE_PRETASK_ACCESS       = "createPretaskAccess";
  const CREATE_SUPERTASK_ACCESS     = "createSupertaskAccess";
  const VIEW_FILE_ACCESS            = array("viewFileAccess", DAccessControl::MANAGE_FILE_ACCESS, DAccessControl::ADD_FILE_ACCESS);
  const MANAGE_FILE_ACCESS          = "manageFileAccess";
  const ADD_FILE_ACCESS             = "addFileAccess";
  const CRACKER_BINARY_ACCESS       = "crackerBinaryAccess";
  const SERVER_CONFIG_ACCESS        = "serverConfigAccess";
  const USER_CONFIG_ACCESS          = "userConfigAccess";
  
  // special access definitions for public access pages and pages which are viewable if logged in
  const PUBLIC_ACCESS = "publicAccess";
  const LOGIN_ACCESS  = "loginAccess";
  
  /**
   * @param $access string
   * @return string description
   */
  public static function getDescription($access) {
    //TODO: return description text for this access string
    return "";
  }
}

/**
 * Class DViewControl
 * This defines the permissions required to view the according page
 */
class DViewControl {
  const ABOUT_VIEW_PERM          = DAccessControl::PUBLIC_ACCESS;
  const ACCESS_VIEW_PERM         = DAccessControl::USER_CONFIG_ACCESS;
  const ACCOUNT_VIEW_PERM        = DAccessControl::LOGIN_ACCESS;
  const AGENTS_VIEW_PERM         = DAccessControl::VIEW_AGENT_ACCESS;
  const BINARIES_VIEW_PERM       = DAccessControl::SERVER_CONFIG_ACCESS;
  const CHUNKS_VIEW_PERM         = DAccessControl::VIEW_TASK_ACCESS;
  const CONFIG_VIEW_PERM         = DAccessControl::SERVER_CONFIG_ACCESS;
  const CRACKERS_VIEW_PERM       = DAccessControl::CRACKER_BINARY_ACCESS;
  const FILES_VIEW_PERM          = DAccessControl::VIEW_FILE_ACCESS;
  const FORGOT_VIEW_PERM         = DAccessControl::PUBLIC_ACCESS;
  const GETFILE_VIEW_PERM        = DAccessControl::PUBLIC_ACCESS;
  const GETHASHLIST_VIEW_PERM    = DAccessControl::PUBLIC_ACCESS;
  const GROUPS_VIEW_PERM         = DAccessControl::USER_CONFIG_ACCESS;
  const HASHES_VIEW_PERM         = DAccessControl::VIEW_HASHLIST_ACCESS;
  const HASHLISTS_VIEW_PERM      = DAccessControl::VIEW_HASHLIST_ACCESS;
  const HASHTYPES_VIEW_PERM      = DAccessControl::SERVER_CONFIG_ACCESS;
  const HELP_VIEW_PERM           = DAccessControl::PUBLIC_ACCESS;
  const INDEX_VIEW_PERM          = DAccessControl::PUBLIC_ACCESS;
  const LOG_VIEW_PERM            = DAccessControl::PUBLIC_ACCESS;
  const LOGIN_VIEW_PERM          = DAccessControl::PUBLIC_ACCESS;
  const LOGOUT_VIEW_PERM         = DAccessControl::LOGIN_ACCESS;
  const NOTIFICATIONS_VIEW_PERM  = DAccessControl::LOGIN_ACCESS;
  const PRETASKS_VIEW_PERM       = DAccessControl::VIEW_TASK_ACCESS;
  const SEARCH_VIEW_PERM         = DAccessControl::VIEW_HASHLIST_ACCESS;
  const SUPERHASHLISTS_VIEW_PERM = DAccessControl::VIEW_HASHLIST_ACCESS;
  const SUPERTASKS_VIEW_PERM     = DAccessControl::VIEW_TASK_ACCESS;
  const TASKS_VIEW_PERM          = DAccessControl::VIEW_TASK_ACCESS;
  const USERS_VIEW_PERM          = DAccessControl::USER_CONFIG_ACCESS;
}