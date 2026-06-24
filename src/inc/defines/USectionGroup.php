<?php

namespace Hashtopolis\inc\defines;

class USectionGroup extends UApi {
  const LIST_GROUPS        = "listGroups";
  const GET_GROUP          = "getGroup";
  const CREATE_GROUP       = "createGroup";
  const ABORT_CHUNKS_GROUP = "abortChunksGroup";
  const DELETE_GROUP       = "deleteGroup";
  
  const ADD_AGENT    = "addAgent";
  const ADD_USER     = "addUser";
  const REMOVE_AGENT = "removeAgent";
  const REMOVE_USER  = "removeUser";
  
  public function describe($constant) {
    return match ($constant) {
      USectionGroup::LIST_GROUPS => "List all groups",
      USectionGroup::GET_GROUP => "Get details of a group",
      USectionGroup::CREATE_GROUP => "Create new groups",
      USectionGroup::ABORT_CHUNKS_GROUP => "Abort all chunks dispatched to agents of this group",
      USectionGroup::DELETE_GROUP => "Delete groups",
      USectionGroup::ADD_AGENT => "Add agents to groups",
      USectionGroup::ADD_USER => "Add users to groups",
      USectionGroup::REMOVE_AGENT => "Remove agents from groups",
      USectionGroup::REMOVE_USER => "Remove users from groups",
      default => "__" . $constant . "__",
    };
  }
}