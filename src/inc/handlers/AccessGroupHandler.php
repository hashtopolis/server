<?php

namespace Hashtopolis\inc\handlers;

use Hashtopolis\inc\utils\AccessControl;
use Hashtopolis\inc\utils\AccessGroupUtils;
use Exception;
use Hashtopolis\inc\defines\DAccessGroupAction;
use Hashtopolis\inc\UI;

class AccessGroupHandler implements Handler {
  public function __construct($configId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DAccessGroupAction::CREATE_GROUP:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::CREATE_GROUP_PERM);
          $group = AccessGroupUtils::createGroup($_POST['groupName']);
          header("Location: groups.php?id=" . $group->getId());
          die();
        case DAccessGroupAction::DELETE_GROUP:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::DELETE_GROUP_PERM);
          AccessGroupUtils::deleteGroup($_POST['groupId']);
          break;
        case DAccessGroupAction::REMOVE_USER:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::REMOVE_USER_PERM);
          AccessGroupUtils::removeUser($_POST['userId'], $_POST['groupId']);
          break;
        case DAccessGroupAction::REMOVE_AGENT:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::REMOVE_AGENT_PERM);
          AccessGroupUtils::removeAgent($_POST['agentId'], $_POST['groupId']);
          break;
        case DAccessGroupAction::ADD_USER:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::ADD_USER_PERM);
          AccessGroupUtils::addUser($_POST['userId'], $_POST['groupId']);
          break;
        case DAccessGroupAction::ADD_AGENT:
          AccessControl::getInstance()->checkPermission(DAccessGroupAction::ADD_AGENT_PERM);
          AccessGroupUtils::addAgent($_POST['agentId'], $_POST['groupId']);
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (Exception $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
}