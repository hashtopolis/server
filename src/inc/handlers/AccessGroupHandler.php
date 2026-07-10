<?php

namespace Hashtopolis\inc\handlers;

use Hashtopolis\inc\utils\AccessControl;
use Hashtopolis\inc\utils\AccessGroupUtils;
use Throwable;
use Hashtopolis\inc\defines\DAccessGroupAction;
use Hashtopolis\inc\UI;

class AccessGroupHandler implements Handler {
  public function __construct($configId = null) {
    //we need nothing to load
  }
  
  public function handle(string $action): void {
    try {
      switch ($action) {
        case DAccessGroupAction::CREATE_GROUP:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::CREATE_GROUP_PERM);
          $group = AccessGroupUtils::createGroup($_POST['groupName']);
          header("Location: groups.php?id=" . $group->getId());
          die();
        case DAccessGroupAction::DELETE_GROUP:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::DELETE_GROUP_PERM);
          AccessGroupUtils::deleteGroup(intval($_POST['groupId']));
          break;
        case DAccessGroupAction::REMOVE_USER:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::REMOVE_USER_PERM);
          AccessGroupUtils::removeUser(intval($_POST['userId']), intval($_POST['groupId']));
          break;
        case DAccessGroupAction::REMOVE_AGENT:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::REMOVE_AGENT_PERM);
          AccessGroupUtils::removeAgent(intval($_POST['agentId']), intval($_POST['groupId']));
          break;
        case DAccessGroupAction::ADD_USER:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::ADD_USER_PERM);
          AccessGroupUtils::addUser(intval($_POST['userId']), intval($_POST['groupId']));
          break;
        case DAccessGroupAction::ADD_AGENT:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::ADD_AGENT_PERM);
          AccessGroupUtils::addAgent(intval($_POST['agentId']), intval($_POST['groupId']));
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (Throwable $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
}