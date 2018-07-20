<?php

class AccountHandler implements Handler {
  private $user;
  
  public function __construct($userId = null) {
    global $FACTORIES;
    
    if ($userId == null) {
      $this->user = null;
      return;
    }
    
    $this->user = $FACTORIES::getUserFactory()->get($userId);
    if ($this->user == null) {
      UI::printError("FATAL", "User with ID $userId not found!");
    }
  }
  
  public function handle($action) {
    /** @var $LOGIN Login */
    global $OBJECTS, $LOGIN, $ACCESS_CONTROL;
    
    try {
      switch ($action) {
        case DAccountAction::SET_EMAIL:
          $ACCESS_CONTROL->checkPermission(DAccountAction::SET_EMAIL_PERM);
          AccountUtils::setEmail($_POST['email'], $LOGIN->getUser());
          UI::addMessage(UI::SUCCESS, "Email updated successfully!");
          break;
        case DAccountAction::YUBIKEY_DISABLE:
          $ACCESS_CONTROL->checkPermission(DAccountAction::YUBIKEY_DISABLE_PERM);
          AccountUtils::setOTP(-1, $action, $LOGIN->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::YUBIKEY_ENABLE:
          $ACCESS_CONTROL->checkPermission(DAccountAction::YUBIKEY_ENABLE_PERM);
          AccountUtils::setOTP(0, $action, $LOGIN->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::SET_OTP1:
          $ACCESS_CONTROL->checkPermission(DAccountAction::SET_OTP1_PERM);
          AccountUtils::setOTP(1, $action, $LOGIN->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::SET_OTP2:
          $ACCESS_CONTROL->checkPermission(DAccountAction::SET_OTP2_PERM);
          AccountUtils::setOTP(2, $action, $LOGIN->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::SET_OTP3:
          $ACCESS_CONTROL->checkPermission(DAccountAction::SET_OTP3_PERM);
          AccountUtils::setOTP(3, $action, $LOGIN->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::SET_OTP4:
          $ACCESS_CONTROL->checkPermission(DAccountAction::SET_OTP4_PERM);
          AccountUtils::setOTP(4, $action, $LOGIN->getUser(), [$_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4']]);
          UI::addMessage(UI::SUCCESS, "OTP updated successfully!");
          break;
        case DAccountAction::UPDATE_LIFETIME:
          $ACCESS_CONTROL->checkPermission(DAccountAction::UPDATE_LIFETIME_PERM);
          AccountUtils::updateSessionLifetime($_POST['lifetime'], $LOGIN->getUser());
          UI::addMessage(UI::SUCCESS, "Updated session lifetime successfully!");
          break;
        case DAccountAction::CHANGE_PASSWORD:
          $ACCESS_CONTROL->checkPermission(DAccountAction::CHANGE_PASSWORD_PERM);
          AccountUtils::changePassword($_POST['oldpass'], $_POST['newpass'], $_POST['reppass'], $LOGIN->getUser());
          UI::addMessage(UI::SUCCESS, "Password was updated successfully!");
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (HTException $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
    
    $OBJECTS['user'] = $LOGIN->getUser();
  }
}