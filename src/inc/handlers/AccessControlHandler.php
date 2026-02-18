<?php

namespace Hashtopolis\inc\handlers;

use Hashtopolis\inc\utils\AccessControl;
use Hashtopolis\inc\utils\AccessControlUtils;
use Exception;
use Hashtopolis\inc\defines\DAccessControlAction;
use Hashtopolis\inc\UI;

class AccessControlHandler implements Handler {
  public function __construct($groupId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DAccessControlAction::CREATE_GROUP:
          AccessControl::getInstance()->checkPermission(DAccessControlAction::CREATE_GROUP_PERM);
          $group = AccessControlUtils::createGroup($_POST['groupName']);
          header("Location: access.php?id=" . $group->getId());
          die();
        case DAccessControlAction::DELETE_GROUP:
          AccessControl::getInstance()->checkPermission(DAccessControlAction::DELETE_GROUP_PERM);
          AccessControlUtils::deleteGroup($_POST['groupId']);
          break;
        case DAccessControlAction::EDIT:
          AccessControl::getInstance()->checkPermission(DAccessControlAction::EDIT_PERM);
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
    catch (Exception $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
}