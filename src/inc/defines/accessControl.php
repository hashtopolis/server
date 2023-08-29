<?php

class DAccessControl {
  const VIEW_HASHLIST_ACCESS        = array("viewHashlistAccess", DAccessControl::CREATE_HASHLIST_ACCESS, DAccessControl::MANAGE_HASHLIST_ACCESS);
  const MANAGE_HASHLIST_ACCESS      = "manageHashlistAccess";
  const CREATE_HASHLIST_ACCESS      = "createHashlistAccess";
  const CREATE_SUPERHASHLIST_ACCESS = "createSuperhashlistAccess";
  const VIEW_HASHES_ACCESS          = "viewHashesAccess";
  const VIEW_AGENT_ACCESS           = array("viewAgentsAccess", DAccessControl::MANAGE_AGENT_ACCESS, DAccessControl::CREATE_AGENT_ACCESS);
  const MANAGE_AGENT_ACCESS         = "manageAgentAccess";
  const CREATE_AGENT_ACCESS         = "createAgentAccess";
  const VIEW_TASK_ACCESS            = array("viewTaskAccess", DAccessControl::CREATE_TASK_ACCESS[0], DAccessControl::MANAGE_TASK_ACCESS);
  const RUN_TASK_ACCESS             = array("runTaskAccess");
  const CREATE_TASK_ACCESS          = array("createTaskAccess");
  const MANAGE_TASK_ACCESS          = "manageTaskAccess";
  const VIEW_PRETASK_ACCESS         = array("viewPretaskAccess", DAccessControl::CREATE_PRETASK_ACCESS, DAccessControl::MANAGE_PRETASK_ACCESS, DAccessControl::RUN_TASK_ACCESS[0]);
  const CREATE_PRETASK_ACCESS       = "createPretaskAccess";
  const MANAGE_PRETASK_ACCESS       = "managePretaskAccess";
  const VIEW_SUPERTASK_ACCESS       = array("viewSupertaskAccess", DAccessControl::CREATE_SUPERTASK_ACCESS, DAccessControl::MANAGE_SUPERTASK_ACCESS);
  const CREATE_SUPERTASK_ACCESS     = "createSupertaskAccess";
  const MANAGE_SUPERTASK_ACCESS     = "manageSupertaskAccess";
  const VIEW_FILE_ACCESS            = array("viewFileAccess", DAccessControl::MANAGE_FILE_ACCESS, DAccessControl::ADD_FILE_ACCESS);
  const MANAGE_FILE_ACCESS          = "manageFileAccess";
  const ADD_FILE_ACCESS             = "addFileAccess";
  const CRACKER_BINARY_ACCESS       = "crackerBinaryAccess";
  const SERVER_CONFIG_ACCESS        = "serverConfigAccess";
  const USER_CONFIG_ACCESS          = "userConfigAccess";
  const MANAGE_ACCESS_GROUP_ACCESS  = "manageAccessGroupAccess";
  const VIEW_BENCHMARK_ACCESS       = "ViewBenchmarkAccess";
  const DELETE_BENCHMARK_ACCESS     = "DeleteBenchmarkAccess";
  
  // special access definitions for public access pages and pages which are viewable if logged in
  const PUBLIC_ACCESS = "publicAccess";
  const LOGIN_ACCESS  = "loginAccess";
  
  static function getConstants() {
    try {
      $oClass = new ReflectionClass(__CLASS__);
    }
    catch (ReflectionException $e) {
      die("Exception: " . $e->getMessage());
    }
    return $oClass->getConstants();
  }
  
  /**
   * @param $access string
   * @return string description
   */
  public static function getDescription($access) {
    if (is_array($access)) {
      $access = $access[0];
    }
    switch ($access) {
      case DAccessControl::VIEW_HASHLIST_ACCESS[0]:
        return "Can view Hashlists";
      case DAccessControl::MANAGE_HASHLIST_ACCESS:
        return "Can manage hashlists";
      case DAccessControl::CREATE_HASHLIST_ACCESS:
        return "Can create hashlists";
      case DAccessControl::CREATE_SUPERHASHLIST_ACCESS:
        return "Can create superhashlists";
      case DAccessControl::VIEW_AGENT_ACCESS[0]:
        return "Can view agents<br>Also granted with manage/create agents permission.";
      case DAccessControl::MANAGE_AGENT_ACCESS:
        return "Can manage agents";
      case DAccessControl::CREATE_AGENT_ACCESS:
        return "Can create agents";
      case DAccessControl::VIEW_TASK_ACCESS[0]:
        return "Can view tasks<br>Also granted with change/create tasks permission.";
      case DAccessControl::RUN_TASK_ACCESS[0]:
        return "Can run preconfigured tasks";
      case DAccessControl::CREATE_TASK_ACCESS[0]:
        return "Can create/delete tasks";
      case DAccessControl::CREATE_PRETASK_ACCESS:
        return "Can create/delete preconfigured tasks";
      case DAccessControl::CREATE_SUPERTASK_ACCESS:
        return "Can create/delete supertasks";
      case DAccessControl::VIEW_FILE_ACCESS[0]:
        return "Can view files<br>Also granted with manage/add files permission.";
      case DAccessControl::MANAGE_FILE_ACCESS:
        return "Can manage files";
      case DAccessControl::ADD_FILE_ACCESS:
        return "Can add files";
      case DAccessControl::CRACKER_BINARY_ACCESS:
        return "Can configure cracker binaries";
      case DAccessControl::SERVER_CONFIG_ACCESS:
        return "Can access server configuration";
      case DAccessControl::USER_CONFIG_ACCESS:
        return "Can manage users";
      case DAccessControl::LOGIN_ACCESS:
        return "Can login and access normal user account features";
      case DAccessControl::VIEW_HASHES_ACCESS:
        return "User can view cracked/uncracked hashes";
      case DAccessControl::MANAGE_TASK_ACCESS:
        return "Can change tasks (set priority, rename, etc.)";
      case DAccessControl::VIEW_PRETASK_ACCESS[0]:
        return "Can view preconfigured tasks<br>Also granted with manage/create preconfigured tasks permission.";
      case DAccessControl::MANAGE_PRETASK_ACCESS:
        return "Can manage preconfigured tasks";
      case DAccessControl::VIEW_SUPERTASK_ACCESS[0]:
        return "Can view preconfigured supertasks<br>Also granted with manage/create supertasks permission.";
      case DAccessControl::MANAGE_SUPERTASK_ACCESS:
        return "Can manage preconfigured supertasks.";
      case DAccessControl::MANAGE_ACCESS_GROUP_ACCESS:
        return "Can manage access groups.";
      case DAccessControl::VIEW_BENCHMARK_ACCESS:
        return "Can view the cached benchmarks";
      case DAccessControl::DELETE_BENCHMARK_ACCESS:
        return "Can delete the cached benchmarks";

    }
    return "__" . $access . "__";
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
  const GROUPS_VIEW_PERM         = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
  const HASHES_VIEW_PERM         = DAccessControl::VIEW_HASHES_ACCESS;
  const HASHLISTS_VIEW_PERM      = DAccessControl::VIEW_HASHLIST_ACCESS;
  const HASHTYPES_VIEW_PERM      = DAccessControl::SERVER_CONFIG_ACCESS;
  const HELP_VIEW_PERM           = DAccessControl::PUBLIC_ACCESS;
  const INDEX_VIEW_PERM          = DAccessControl::PUBLIC_ACCESS;
  const LOG_VIEW_PERM            = DAccessControl::PUBLIC_ACCESS;
  const LOGIN_VIEW_PERM          = DAccessControl::PUBLIC_ACCESS;
  const LOGOUT_VIEW_PERM         = DAccessControl::LOGIN_ACCESS;
  const NOTIFICATIONS_VIEW_PERM  = DAccessControl::LOGIN_ACCESS;
  const PRETASKS_VIEW_PERM       = DAccessControl::VIEW_PRETASK_ACCESS;
  const SEARCH_VIEW_PERM         = DAccessControl::VIEW_HASHES_ACCESS;
  const SUPERHASHLISTS_VIEW_PERM = DAccessControl::VIEW_HASHLIST_ACCESS;
  const SUPERTASKS_VIEW_PERM     = DAccessControl::VIEW_SUPERTASK_ACCESS;
  const TASKS_VIEW_PERM          = DAccessControl::VIEW_TASK_ACCESS;
  const USERS_VIEW_PERM          = DAccessControl::USER_CONFIG_ACCESS;
  const API_VIEW_PERM            = DAccessControl::USER_CONFIG_ACCESS;
  const HEALTH_VIEW_PERM         = DAccessControl::SERVER_CONFIG_ACCESS;
  const PREPROCESSORS_VIEW_PERM  = DAccessControl::SERVER_CONFIG_ACCESS;
}

class DAccessControlAction {
  const CREATE_GROUP      = "createGroup";
  const CREATE_GROUP_PERM = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
  
  const DELETE_GROUP      = "deleteGroup";
  const DELETE_GROUP_PERM = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
  
  const EDIT      = "edit";
  const EDIT_PERM = DAccessControl::USER_CONFIG_ACCESS;
}