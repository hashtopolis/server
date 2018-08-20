<?php

class AccessGroupHandler implements Handler {
  public function __construct($configId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    global $ACCESS_CONTROL;
    
    try {
      switch ($action) {
        case DAccessGroupAction::CREATE_GROUP:
          $ACCESS_CONTROL->checkPermission(DAccessGroupAction::CREATE_GROUP_PERM);
          $group = AccessGroupUtils::createGroup($_POST['groupName']);
          header("Location: groups.php?id=" . $group->getId());
          die();
        case DAccessGroupAction::DELETE_GROUP:
          $ACCESS_CONTROL->checkPermission(DAccessGroupAction::DELETE_GROUP_PERM);
          AccessGroupUtils::deleteGroup($_POST['groupId']);
          break;
        case DAccessGroupAction::REMOVE_USER:
          $ACCESS_CONTROL->checkPermission(DAccessGroupAction::REMOVE_USER_PERM);
          AccessGroupUtils::removeUser($_POST['userId'], $_POST['groupId']);
          break;
        case DAccessGroupAction::REMOVE_AGENT:
          $ACCESS_CONTROL->checkPermission(DAccessGroupAction::REMOVE_AGENT_PERM);
          AccessGroupUtils::removeAgent($_POST['agentId'], $_POST['groupId']);
          break;
        case DAccessGroupAction::ADD_USER:
          $ACCESS_CONTROL->checkPermission(DAccessGroupAction::ADD_USER_PERM);
          AccessGroupUtils::addUser($_POST['userId'], $_POST['groupId']);
          break;
        case DAccessGroupAction::ADD_AGENT:
          $ACCESS_CONTROL->checkPermission(DAccessGroupAction::ADD_AGENT_PERM);
          AccessGroupUtils::addAgent($_POST['agentId'], $_POST['groupId']);
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