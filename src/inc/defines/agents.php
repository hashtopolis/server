<?php

define("HTP_AGENT_ARCHIVE", 'https://archive.hashtopolis.org/agent/');

class DAgentBinaryAction {
  const NEW_BINARY      = "newBinary";
  const NEW_BINARY_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const EDIT_BINARY      = "editBinary";
  const EDIT_BINARY_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const DELETE_BINARY      = "deleteBinary";
  const DELETE_BINARY_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const UPGRADE_BINARY      = "upgradeBinary";
  const UPGRADE_BINARY_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const CHECK_UPDATE      = "checkUpdate";
  const CHECK_UPDATE_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
}

class DAgentIgnoreErrors {
  const NO            = 0;
  const IGNORE_SAVE   = 1;
  const IGNORE_NOSAVE = 2;
}

class DAgentStatsType {
  const GPU_TEMP = 1;
  const GPU_UTIL = 2;
  const CPU_UTIL = 3;
}

class DAgentAction {
  const CLEAR_ERRORS      = "clearErrors";
  const CLEAR_ERRORS_PERM = DAccessControl::MANAGE_AGENT_ACCESS;
  
  const RENAME_AGENT      = "renameAgent";
  const RENAME_AGENT_PERM = DAccessControl::MANAGE_AGENT_ACCESS;
  
  const SET_OWNER      = "setOwner";
  const SET_OWNER_PERM = DAccessControl::MANAGE_AGENT_ACCESS;
  
  const SET_TRUSTED      = "setTrusted";
  const SET_TRUSTED_PERM = DAccessControl::MANAGE_AGENT_ACCESS;
  
  const SET_IGNORE      = "setIgnore";
  const SET_IGNORE_PERM = DAccessControl::MANAGE_AGENT_ACCESS;
  
  const SET_PARAMETERS      = "setParameters";
  const SET_PARAMETERS_PERM = DAccessControl::MANAGE_AGENT_ACCESS;
  
  const SET_ACTIVE      = "setActive";
  const SET_ACTIVE_PERM = DAccessControl::MANAGE_AGENT_ACCESS;
  
  const DELETE_AGENT      = "deleteAgent";
  const DELETE_AGENT_PERM = DAccessControl::MANAGE_AGENT_ACCESS;
  
  const ASSIGN_AGENT      = "assignAgent";
  const ASSIGN_AGENT_PERM = DAccessControl::MANAGE_AGENT_ACCESS;
  
  const CREATE_VOUCHER      = "createVoucher";
  const CREATE_VOUCHER_PERM = DAccessControl::CREATE_AGENT_ACCESS;
  
  const DELETE_VOUCHER      = "deleteVoucher";
  const DELETE_VOUCHER_PERM = DAccessControl::CREATE_AGENT_ACCESS;
  
  const DOWNLOAD_AGENT      = "downloadAgent";
  const DOWNLOAD_AGENT_PERM = DAccessControl::CREATE_AGENT_ACCESS;
  
  const SET_CPU      = "setCpu";
  const SET_CPU_PERM = DAccessControl::MANAGE_AGENT_ACCESS;
}