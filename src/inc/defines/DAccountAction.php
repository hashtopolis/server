<?php

namespace Hashtopolis\inc\defines;

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
