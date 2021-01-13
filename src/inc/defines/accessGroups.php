<?php

// access levels for user groups
class DAccessLevel {
  // if you change any of them here, you need to check if this is consistent with the database
  const VIEW_ONLY     = 1;
  const READ_ONLY     = 5;
  const USER          = 20;
  const SUPERUSER     = 30;
  const ADMINISTRATOR = 50;
}

class DAccessGroupAction {
  const CREATE_GROUP      = "createGroup";
  const CREATE_GROUP_PERM = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
  
  const DELETE_GROUP      = "deleteGroup";
  const DELETE_GROUP_PERM = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
  
  const REMOVE_USER      = "removeUser";
  const REMOVE_USER_PERM = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
  
  const REMOVE_AGENT      = "removeAgent";
  const REMOVE_AGENT_PERM = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
  
  const ADD_USER      = "addUser";
  const ADD_USER_PERM = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
  
  const ADD_AGENT      = "addAgent";
  const ADD_AGENT_PERM = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
}