<?php

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
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
      case DAccessGroupAction::DELETE_GROUP:
        $this->deleteGroup($_POST['groupId']);
        break;
      case DAccessGroupAction::REMOVE_USER:
        $this->removeUser($_POST['userId'], $_POST['groupId']);
        break;
      case DAccessGroupAction::REMOVE_AGENT:
        $this->removeAgent($_POST['agentId'], $_POST['groupId']);
        break;
      case DAccessGroupAction::ADD_USER:
        $this->addUser($_POST['userId'], $_POST['groupId']);
        break;
      case DAccessGroupAction::ADD_AGENT:
        $this->addAgent($_POST['agentId'], $_POST['groupId']);
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function addUser($userId, $groupId) {
    global $FACTORIES;
    
    $group = $FACTORIES::getAccessGroupFactory()->get($groupId);
    if ($group === null) {
      UI::addMessage(UI::ERROR, "Invalid access group!");
      return;
    }
    $user = $FACTORIES::getUserFactory()->get($userId);
    if ($user === null) {
      UI::addMessage(UI::ERROR, "Invalid user!");
      return;
    }
    $qF1 = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $group->getId(), "=");
    $check = $FACTORIES::getAccessGroupUserFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
    if (sizeof($check) > 0) {
      UI::addMessage(UI::ERROR, "User is already member of this group!");
      return;
    }
    
    $accessGroupUser = new AccessGroupUser(0, $group->getId(), $user->getId());
    $FACTORIES::getAccessGroupUserFactory()->save($accessGroupUser);
  }
  
  private function addAgent($agentId, $groupId) {
    global $FACTORIES;
    
    $group = $FACTORIES::getAccessGroupFactory()->get($groupId);
    if ($group === null) {
      UI::addMessage(UI::ERROR, "Invalid access group!");
      return;
    }
    $agent = $FACTORIES::getAgentFactory()->get($agentId);
    if ($agent === null) {
      UI::addMessage(UI::ERROR, "Invalid agent!");
      return;
    }
    $qF1 = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $group->getId(), "=");
    $check = $FACTORIES::getAccessGroupAgentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
    if (sizeof($check) > 0) {
      UI::addMessage(UI::ERROR, "Agent is already member of this group!");
      return;
    }
    
    $accessGroupAgent = new AccessGroupAgent(0, $group->getId(), $agent->getId());
    $FACTORIES::getAccessGroupAgentFactory()->save($accessGroupAgent);
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