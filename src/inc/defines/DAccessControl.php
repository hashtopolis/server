<?php

namespace Hashtopolis\inc\defines;

use ReflectionClass;
use ReflectionException;

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
    return match ($access) {
      DAccessControl::VIEW_HASHLIST_ACCESS[0] => "Can view Hashlists",
      DAccessControl::MANAGE_HASHLIST_ACCESS => "Can manage hashlists",
      DAccessControl::CREATE_HASHLIST_ACCESS => "Can create hashlists",
      DAccessControl::CREATE_SUPERHASHLIST_ACCESS => "Can create superhashlists",
      DAccessControl::VIEW_AGENT_ACCESS[0] => "Can view agents<br>Also granted with manage/create agents permission.",
      DAccessControl::MANAGE_AGENT_ACCESS => "Can manage agents",
      DAccessControl::CREATE_AGENT_ACCESS => "Can create agents",
      DAccessControl::VIEW_TASK_ACCESS[0] => "Can view tasks<br>Also granted with change/create tasks permission.",
      DAccessControl::RUN_TASK_ACCESS[0] => "Can run preconfigured tasks",
      DAccessControl::CREATE_TASK_ACCESS[0] => "Can create/delete tasks",
      DAccessControl::CREATE_PRETASK_ACCESS => "Can create/delete preconfigured tasks",
      DAccessControl::CREATE_SUPERTASK_ACCESS => "Can create/delete supertasks",
      DAccessControl::VIEW_FILE_ACCESS[0] => "Can view files<br>Also granted with manage/add files permission.",
      DAccessControl::MANAGE_FILE_ACCESS => "Can manage files",
      DAccessControl::ADD_FILE_ACCESS => "Can add files",
      DAccessControl::CRACKER_BINARY_ACCESS => "Can configure cracker binaries",
      DAccessControl::SERVER_CONFIG_ACCESS => "Can access server configuration",
      DAccessControl::USER_CONFIG_ACCESS => "Can manage users",
      DAccessControl::LOGIN_ACCESS => "Can login and access normal user account features",
      DAccessControl::VIEW_HASHES_ACCESS => "User can view cracked/uncracked hashes",
      DAccessControl::MANAGE_TASK_ACCESS => "Can change tasks (set priority, rename, etc.)",
      DAccessControl::VIEW_PRETASK_ACCESS[0] => "Can view preconfigured tasks<br>Also granted with manage/create preconfigured tasks permission.",
      DAccessControl::MANAGE_PRETASK_ACCESS => "Can manage preconfigured tasks",
      DAccessControl::VIEW_SUPERTASK_ACCESS[0] => "Can view preconfigured supertasks<br>Also granted with manage/create supertasks permission.",
      DAccessControl::MANAGE_SUPERTASK_ACCESS => "Can manage preconfigured supertasks.",
      DAccessControl::MANAGE_ACCESS_GROUP_ACCESS => "Can manage access groups.",
      default => "__" . $access . "__",
    };
  }
}
