<?php

namespace Hashtopolis\inc\defines;

class USectionAccess extends UApi {
  const LIST_GROUPS     = "listGroups";
  const GET_GROUP       = "getGroup";
  const CREATE_GROUP    = "createGroup";
  const DELETE_GROUP    = "deleteGroup";
  const SET_PERMISSIONS = "setPermissions";
  
  public function describe($constant) {
    return match ($constant) {
      USectionAccess::LIST_GROUPS => "List permission groups",
      USectionAccess::GET_GROUP => "Get details of a permission group",
      USectionAccess::CREATE_GROUP => "Create a new permission group",
      USectionAccess::DELETE_GROUP => "Delete permission groups",
      USectionAccess::SET_PERMISSIONS => "Update permissions of a group",
      default => "__" . $constant . "__",
    };
  }
}