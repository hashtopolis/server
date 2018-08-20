<?php

class AccessControlHandler implements Handler {
  public function __construct($groupId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    global $ACCESS_CONTROL;
    
    try {
      switch ($action) {
        case DAccessControlAction::CREATE_GROUP:
          $ACCESS_CONTROL->checkPermission(DAccessControlAction::CREATE_GROUP_PERM);
          $group = AccessControlUtils::createGroup($_POST['groupName']);
          header("Location: access.php?id=" . $group->getId());
          die();
        case DAccessControlAction::DELETE_GROUP:
          $ACCESS_CONTROL->checkPermission(DAccessControlAction::DELETE_GROUP_PERM);
          AccessControlUtils::deleteGroup($_POST['groupId']);
          break;
        case DAccessControlAction::EDIT:
          $ACCESS_CONTROL->checkPermission(DAccessControlAction::EDIT_PERM);
          $changes = AccessControlUtils::updateGroupPermissions($_POST['groupId'], $_POST['perm']);
          if ($changes) {
            UI::addMessage(UI::WARN, "NOTE: Some permissions were additionally allowed because of dependencies!");
          }
          break;
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