<?php

namespace Hashtopolis\inc\defines;

class USectionUser extends UApi {
  const LIST_USERS           = "listUsers";
  const GET_USER             = "getUser";
  const CREATE_USER          = "createUser";
  const DISABLE_USER         = "disableUser";
  const ENABLE_USER          = "enableUser";
  const SET_USER_PASSWORD    = "setUserPassword";
  const SET_USER_RIGHT_GROUP = "setUserRightGroup";
  
  public function describe($constant) {
    return match ($constant) {
      USectionUser::LIST_USERS => "List all users",
      USectionUser::GET_USER => "Get details of a user",
      USectionUser::CREATE_USER => "Create new users",
      USectionUser::DISABLE_USER => "Disable a user account",
      USectionUser::ENABLE_USER => "Enable a user account",
      USectionUser::SET_USER_PASSWORD => "Set a user's password",
      USectionUser::SET_USER_RIGHT_GROUP => "Change the permission group for a user",
      default => "__" . $constant . "__",
    };
  }
}