<?php

namespace Hashtopolis\inc\defines;

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