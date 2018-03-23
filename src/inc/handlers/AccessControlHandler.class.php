<?php

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\QueryFilter;
use DBA\RightGroup;
use DBA\TaskWrapper;
use DBA\User;

class AccessControlHandler implements Handler {
  public function __construct($groupId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    global $ACCESS_CONTROL;
    
    switch ($action) {
      case DAccessControlAction::CREATE_GROUP:
        $ACCESS_CONTROL->checkPermission(DAccessControlAction::CREATE_GROUP_PERM);
        $this->createGroup($_POST['groupName']);
        break;
      case DAccessControlAction::DELETE_GROUP:
        $ACCESS_CONTROL->checkPermission(DAccessControlAction::DELETE_GROUP_PERM);
        $this->deleteGroup($_POST['groupId']);
        break;
      case DAccessControlAction::EDIT:
        $ACCESS_CONTROL->checkPermission(DAccessControlAction::EDIT_PERM);
        $this->saveEdit($_POST['groupId']);
        break;
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function deleteGroup($groupId) {
    global $FACTORIES;
    
    $group = $FACTORIES::getRightGroupFactory()->get($groupId);
    if ($group === null) {
      UI::addMessage(UI::ERROR, "Invalid group!");
      return;
    }
    
    $qF = new QueryFilter(User::RIGHT_GROUP_ID, $group->getId(), "=");
    $count = $FACTORIES::getUserFactory()->countFilter(array($FACTORIES::FILTER => $qF));
    if ($count > 0) {
      UI::addMessage(UI::ERROR, "You cannot delete a group which has still users belonging to it!");
      return;
    }
    
    // delete permission group
    $FACTORIES::getRightGroupFactory()->delete($group);
  }
  
  private function createGroup($groupName) {
    global $FACTORIES;
    
    if (strlen($groupName) == 0 || strlen($groupName) > DLimits::ACCESS_GROUP_MAX_LENGTH) {
      UI::addMessage(UI::ERROR, "Permission group name is too short or too long!");
      return;
    }
    
    $qF = new QueryFilter(RightGroup::GROUP_NAME, $groupName, "=");
    $check = $FACTORIES::getRightGroupFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($check !== null) {
      UI::addMessage(UI::ERROR, "There is already an permission group with the same name!");
      return;
    }
    $group = new RightGroup(0, $groupName, "[]");
    $group = $FACTORIES::getRightGroupFactory()->save($group);
    if ($group !== null) {
      header("Location: access.php?id=" . $group->getId());
      die();
    }
    UI::addMessage(UI::ERROR, "Something went wrong when creating the permission group!");
  }
}