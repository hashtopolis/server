<?php

class UsersHandler implements Handler {
  public function __construct($userId = null) {
    //nothing to do
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DUserAction::DELETE_USER:
          AccessControl::getInstance()->checkPermission(DUserAction::DELETE_USER_PERM);
          UserUtils::deleteUser($_POST['user'], Login::getInstance()->getUser());
          header("Location: users.php");
          die();
        case DUserAction::ENABLE_USER:
          AccessControl::getInstance()->checkPermission(DUserAction::ENABLE_USER_PERM);
          UserUtils::enableUser($_POST['user']);
          UI::addMessage(UI::SUCCESS, "User account enabled successfully!");
          break;
        case DUserAction::DISABLE_USER:
          AccessControl::getInstance()->checkPermission(DUserAction::DISABLE_USER_PERM);
          UserUtils::disableUser($_POST['user'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "User was disabled successfully!");
          break;
        case DUserAction::ENABLE_LDAP:
          AccessControl::getInstance()->checkPermission(DUserAction::ENABLE_LDAP_PERM);
          UserUtils::enableLDAP($_POST['user']);
          UI::addMessage(UI::SUCCESS, "LDAP enabled successfully!");
          break;
        case DUserAction::DISABLE_LDAP:
          AccessControl::getInstance()->checkPermission(DUserAction::DISABLE_LDAP_PERM);
          UserUtils::disableLDAP($_POST['user'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "LDAP was disabled successfully!");
          break;
        case DUserAction::SET_RIGHTS:
          AccessControl::getInstance()->checkPermission(DUserAction::SET_RIGHTS_PERM);
          UserUtils::setRights($_POST['user'], $_POST['group'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Updated user rights successfully!");
          break;
        case DUserAction::SET_PASSWORD:
          AccessControl::getInstance()->checkPermission(DUserAction::SET_PASSWORD_PERM);
          UserUtils::setPassword($_POST['user'], $_POST['pass'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "User password was updated successfully!");
          break;
        case DUserAction::CREATE_USER:
          AccessControl::getInstance()->checkPermission(DUserAction::CREATE_USER_PERM);
          UserUtils::createUser($_POST['username'], $_POST['email'], $_POST['group'], Login::getInstance()->getUser());
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