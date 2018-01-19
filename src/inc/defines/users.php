<?php

class DAccountAction {
  const SET_EMAIL       = "setEmail";
  const YUBIKEY_DISABLE = "yubikeyDisable";
  const YUBIKEY_ENABLE  = "yubikeyEnable";
  const SET_OTP1        = "setOTP1";
  const SET_OTP2        = "setOTP2";
  const SET_OTP3        = "setOTP3";
  const SET_OTP4        = "setOTP4";
  const UPDATE_LIFETIME = "updateLifetime";
  const CHANGE_PASSWORD = "changePassword";
}

class DUserAction {
  const DELETE_USER  = "deleteUser";
  const ENABLE_USER  = "enableUser";
  const DISABLE_USER = "disableUser";
  const SET_RIGHTS   = "setRights";
  const SET_PASSWORD = "setPassword";
  const CREATE_USER  = "createUser";
}