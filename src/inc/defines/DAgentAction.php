<?php

namespace Hashtopolis\inc\defines;

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