<?php

use DBA\AccessGroup;
use DBA\QueryFilter;

class AccessGroupHandler implements Handler {
  public function __construct($configId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    switch ($action) {
      case DAccessGroupAction::CREATE_GROUP:
        $this->createGroup($_POST['groupName']);
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function createGroup($groupName) {
    global $FACTORIES;
    
    if (strlen($groupName) == 0 || strlen($groupName) > DLimits::ACCESS_GROUP_MAX_LENGTH) {
      UI::addMessage(UI::ERROR, "Access group name is too short or too long!");
      return;
    }
    
    $qF = new QueryFilter(AccessGroup::GROUP_NAME, $groupName, "=");
    $check = $FACTORIES::getAccessGroupFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($check !== null) {
      UI::addMessage(UI::ERROR, "There is already an access group with the same name!");
      return;
    }
    $group = new AccessGroup(0, $groupName);
    $group = $FACTORIES::getAccessGroupFactory()->save($group);
    if ($group !== null) {
      header("Location: groups.php?id=" . $group->getId());
      die();
    }
    UI::addMessage(UI::ERROR, "Something went wrong when creating the group!");
  }
}