<?php

namespace Hashtopolis\inc\defines;

class DAccessControlAction {
  const CREATE_GROUP      = "createGroup";
  const CREATE_GROUP_PERM = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
  
  const DELETE_GROUP      = "deleteGroup";
  const DELETE_GROUP_PERM = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
  
  const EDIT      = "edit";
  const EDIT_PERM = DAccessControl::USER_CONFIG_ACCESS;
}