<?php

class DHashlistAction {
  const APPLY_PRECONFIGURED_TASKS      = "applyPreconfiguredTasks";
  const APPLY_PRECONFIGURED_TASKS_PERM = DAccessControl::RUN_TASK_ACCESS;
  
  const CREATE_WORDLIST      = "createWordlist";
  const CREATE_WORDLIST_PERM = DAccessControl::ADD_FILE_ACCESS;
  
  const SET_SECRET      = "setSecret";
  const SET_SECRET_PERM = DAccessControl::MANAGE_HASHLIST_ACCESS;
  
  const SET_ARCHIVED      = "setArchived";
  const SET_ARCHIVED_PERM = DAccessControl::MANAGE_HASHLIST_ACCESS;
  
  const RENAME_HASHLIST      = "renameHashlist";
  const RENAME_HASHLIST_PERM = DAccessControl::MANAGE_HASHLIST_ACCESS;
  
  const PROCESS_ZAP      = "processZap";
  const PROCESS_ZAP_PERM = DAccessControl::MANAGE_HASHLIST_ACCESS;
  
  const EXPORT_HASHLIST      = "exportHashlist";
  const EXPORT_HASHLIST_PERM = DAccessControl::ADD_FILE_ACCESS;
  
  const ZAP_HASHLIST      = "zapHashlist";
  const ZAP_HASHLIST_PERM = DAccessControl::MANAGE_HASHLIST_ACCESS;
  
  const DELETE_HASHLIST      = "deleteHashlist";
  const DELETE_HASHLIST_PERM = DAccessControl::MANAGE_HASHLIST_ACCESS;
  
  const CREATE_HASHLIST      = "createHashlist";
  const CREATE_HASHLIST_PERM = DAccessControl::CREATE_HASHLIST_ACCESS;
  
  const CREATE_SUPERHASHLIST      = "createSuperhashlist";
  const CREATE_SUPERHASHLIST_PERM = DAccessControl::CREATE_SUPERHASHLIST_ACCESS;
  
  const CREATE_LEFTLIST      = "createLeftlist";
  const CREATE_LEFTLIST_PERM = DAccessControl::ADD_FILE_ACCESS;
  
  const EDIT_NOTES      = "editNotes";
  const EDIT_NOTES_PERM = DAccessControl::MANAGE_HASHLIST_ACCESS;
  
  const SET_ACCESS_GROUP      = "setAccessGroup";
  const SET_ACCESS_GROUP_PERM = DAccessControl::MANAGE_HASHLIST_ACCESS;
}

class DHashtypeAction {
  const DELETE_HASHTYPE      = "deleteHashtype";
  const DELETE_HASHTYPE_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
  
  const ADD_HASHTYPE      = "addHashtype";
  const ADD_HASHTYPE_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
}

// hashlist formats
class DHashlistFormat {
  const PLAIN         = 0;
  const WPA           = 1;
  const BINARY        = 2;
  const SUPERHASHLIST = 3;
}
