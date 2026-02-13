<?php

namespace Hashtopolis\inc\defines;

class USectionAccount extends UApi {
  const GET_INFORMATION    = "getInformation";
  const SET_EMAIL          = "setEmail";
  const SET_SESSION_LENGTH = "setSessionLength";
  const CHANGE_PASSWORD    = "changePassword";
  
  public function describe($constant) {
    return match ($constant) {
      USectionAccount::GET_INFORMATION => "Get account information",
      USectionAccount::SET_EMAIL => "Change email",
      USectionAccount::SET_SESSION_LENGTH => "Update session length",
      USectionAccount::CHANGE_PASSWORD => "Change password",
      default => "__" . $constant . "__",
    };
  }
}