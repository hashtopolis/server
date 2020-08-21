<?php

class DAccountAction {
  const SET_EMAIL      = "setEmail";
  const SET_EMAIL_PERM = DAccessControl::LOGIN_ACCESS;
  
  const YUBIKEY_DISABLE      = "yubikeyDisable";
  const YUBIKEY_DISABLE_PERM = DAccessControl::LOGIN_ACCESS;
  
  const YUBIKEY_ENABLE      = "yubikeyEnable";
  const YUBIKEY_ENABLE_PERM = DAccessControl::LOGIN_ACCESS;
  
  const SET_OTP1      = "setOTP1";
  const SET_OTP1_PERM = DAccessControl::LOGIN_ACCESS;
  
  const SET_OTP2      = "setOTP2";
  const SET_OTP2_PERM = DAccessControl::LOGIN_ACCESS;
  
  const SET_OTP3      = "setOTP3";
  const SET_OTP3_PERM = DAccessControl::LOGIN_ACCESS;
  
  const SET_OTP4      = "setOTP4";
  const SET_OTP4_PERM = DAccessControl::LOGIN_ACCESS;
  
  const UPDATE_LIFETIME      = "updateLifetime";
  const UPDATE_LIFETIME_PERM = DAccessControl::LOGIN_ACCESS;
  
  const CHANGE_PASSWORD      = "changePassword";
  const CHANGE_PASSWORD_PERM = DAccessControl::LOGIN_ACCESS;
}

class DUserAction {
  const DELETE_USER      = "deleteUser";
  const DELETE_USER_PERM = DAccessControl::USER_CONFIG_ACCESS;
  
  const ENABLE_USER      = "enableUser";
  const ENABLE_USER_PERM = DAccessControl::USER_CONFIG_ACCESS;
  
  const DISABLE_USER      = "disableUser";
  const DISABLE_USER_PERM = DAccessControl::USER_CONFIG_ACCESS;

  const ENABLE_LDAP      = "enableLDAP";
  const ENABLE_LDAP_PERM = DAccessControl::USER_CONFIG_ACCESS;
  
  const DISABLE_LDAP      = "disableLDAP";
  const DISABLE_LDAP_PERM = DAccessControl::USER_CONFIG_ACCESS;
  
  const SET_RIGHTS      = "setRights";
  const SET_RIGHTS_PERM = DAccessControl::USER_CONFIG_ACCESS;
  
  const SET_PASSWORD      = "setPassword";
  const SET_PASSWORD_PERM = DAccessControl::USER_CONFIG_ACCESS;
  
  const CREATE_USER      = "createUser";
  const CREATE_USER_PERM = DAccessControl::USER_CONFIG_ACCESS;
}

class DForgotAction {
  const RESET      = "reset";
  const RESET_PERM = DAccessControl::PUBLIC_ACCESS;
}