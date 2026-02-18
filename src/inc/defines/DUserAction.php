<?php

namespace Hashtopolis\inc\defines;

class DUserAction {
  const DELETE_USER      = "deleteUser";
  const DELETE_USER_PERM = DAccessControl::USER_CONFIG_ACCESS;
  
  const ENABLE_USER      = "enableUser";
  const ENABLE_USER_PERM = DAccessControl::USER_CONFIG_ACCESS;
  
  const DISABLE_USER      = "disableUser";
  const DISABLE_USER_PERM = DAccessControl::USER_CONFIG_ACCESS;
  
  const SET_RIGHTS      = "setRights";
  const SET_RIGHTS_PERM = DAccessControl::USER_CONFIG_ACCESS;
  
  const SET_PASSWORD      = "setPassword";
  const SET_PASSWORD_PERM = DAccessControl::USER_CONFIG_ACCESS;
  
  const CREATE_USER      = "createUser";
  const CREATE_USER_PERM = DAccessControl::USER_CONFIG_ACCESS;
}