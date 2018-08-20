<?php

class UsersHandler implements Handler {
  public function __construct($userId = null) {
    //nothing to do
  }
  
  public function handle($action) {
    global $ACCESS_CONTROL;
    
    try {
      switch ($action) {
        case DUserAction::DELETE_USER:
          $ACCESS_CONTROL->checkPermission(DUserAction::DELETE_USER_PERM);
          UserUtils::deleteUser($_POST['user'], $ACCESS_CONTROL->getUser());
          header("Location: users.php");
          die();
        case DUserAction::ENABLE_USER:
          $ACCESS_CONTROL->checkPermission(DUserAction::ENABLE_USER_PERM);
          UserUtils::enableUser($_POST['user']);
          UI::addMessage(UI::SUCCESS, "User account enabled successfully!");
          break;
        case DUserAction::DISABLE_USER:
          $ACCESS_CONTROL->checkPermission(DUserAction::DISABLE_USER_PERM);
          UserUtils::disableUser($_POST['user'], $ACCESS_CONTROL->getUser());
          UI::addMessage(UI::SUCCESS, "User was disabled successfully!");
          break;
        case DUserAction::SET_RIGHTS:
          $ACCESS_CONTROL->checkPermission(DUserAction::SET_RIGHTS_PERM);
          UserUtils::setRights($_POST['user'], $_POST['group'], $ACCESS_CONTROL->getUser());
          UI::addMessage(UI::SUCCESS, "Updated user rights successfully!");
          break;
        case DUserAction::SET_PASSWORD:
          $ACCESS_CONTROL->checkPermission(DUserAction::SET_PASSWORD_PERM);
          UserUtils::setPassword($_POST['user'], $_POST['pass'], $ACCESS_CONTROL->getUser());
          UI::addMessage(UI::SUCCESS, "User password was updated successfully!");
          break;
        case DUserAction::CREATE_USER:
          $ACCESS_CONTROL->checkPermission(DUserAction::CREATE_USER_PERM);
          UserUtils::createUser($_POST['username'], $_POST['email'], $_POST['group'], $ACCESS_CONTROL->getUser());
          header("Location: users.php");
          die();
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (HTException $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
}