<?php

use DBA\Factory;

class AccountHandler implements Handler {
  private $user;
  
  public function __construct($userId = null) {
    if ($userId == null) {
      $this->user = null;
      return;
    }
    
    $this->user = Factory::getUserFactory()->get($userId);
    if ($this->user == null) {
      UI::printError("FATAL", "User with ID $userId not found!");
    }
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DAccountAction::SET_EMAIL:
          AccessControl::getInstance()->checkPermission(DAccountAction::SET_EMAIL_PERM);
          AccountUtils::setEmail($_POST['email'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Email updated successfully!");
          break;
        case DAccountAction::YUBIKEY_DISABLE:
          AccessControl::getInstance()->checkPermission(DAccountAction::YUBIKEY_DISABLE_PERM);
          AccountUtils::setOTP(-1, $action, Login::getInstance()->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::YUBIKEY_ENABLE:
          AccessControl::getInstance()->checkPermission(DAccountAction::YUBIKEY_ENABLE_PERM);
          AccountUtils::setOTP(0, $action, Login::getInstance()->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::SET_OTP1:
          AccessControl::getInstance()->checkPermission(DAccountAction::SET_OTP1_PERM);
          AccountUtils::setOTP(1, $action, Login::getInstance()->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::SET_OTP2:
          AccessControl::getInstance()->checkPermission(DAccountAction::SET_OTP2_PERM);
          AccountUtils::setOTP(2, $action, Login::getInstance()->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::SET_OTP3:
          AccessControl::getInstance()->checkPermission(DAccountAction::SET_OTP3_PERM);
          AccountUtils::setOTP(3, $action, Login::getInstance()->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::SET_OTP4:
          AccessControl::getInstance()->checkPermission(DAccountAction::SET_OTP4_PERM);
          AccountUtils::setOTP(4, $action, Login::getInstance()->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::UPDATE_LIFETIME:
          AccessControl::getInstance()->checkPermission(DAccountAction::UPDATE_LIFETIME_PERM);
          AccountUtils::updateSessionLifetime($_POST['lifetime'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Updated session lifetime successfully!");
          break;
        case DAccountAction::CHANGE_PASSWORD:
          AccessControl::getInstance()->checkPermission(DAccountAction::CHANGE_PASSWORD_PERM);
          AccountUtils::changePassword($_POST['oldpass'], $_POST['newpass'], $_POST['reppass'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Password was updated successfully!");
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (Exception $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
    
    UI::add('user', Login::getInstance()->getUser());
  }
}