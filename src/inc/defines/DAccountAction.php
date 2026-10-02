<?php

namespace Hashtopolis\inc\defines;

class DAccountAction {
  const SET_EMAIL      = "setEmail";
  const SET_EMAIL_PERM = DAccessControl::LOGIN_ACCESS;

  const UPDATE_LIFETIME      = "updateLifetime";
  const UPDATE_LIFETIME_PERM = DAccessControl::LOGIN_ACCESS;
  
  const CHANGE_PASSWORD      = "changePassword";
  const CHANGE_PASSWORD_PERM = DAccessControl::LOGIN_ACCESS;
}
