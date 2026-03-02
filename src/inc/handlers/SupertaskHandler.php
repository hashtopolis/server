<?php

namespace Hashtopolis\inc\handlers;

use Hashtopolis\inc\utils\AccessControl;
use Exception;
use Hashtopolis\inc\defines\DSupertaskAction;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\utils\SupertaskUtils;
use Hashtopolis\inc\UI;

class SupertaskHandler implements Handler {
  public function __construct($supertaskId = null) {
    //nothing
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DSupertaskAction::DELETE_SUPERTASK:
          AccessControl::getInstance()->checkPermission(DSupertaskAction::DELETE_SUPERTASK_PERM);
          SupertaskUtils::deleteSupertask($_POST['supertask']);
          break;
        case DSupertaskAction::CREATE_SUPERTASK:
          AccessControl::getInstance()->checkPermission(DSupertaskAction::CREATE_SUPERTASK_PERM);
          SupertaskUtils::createSupertask($_POST['name'], @$_POST['task']);
          break;
        case DSupertaskAction::APPLY_SUPERTASK:
          AccessControl::getInstance()->checkPermission(DSupertaskAction::APPLY_SUPERTASK_PERM);
          SupertaskUtils::runSupertask($_POST['supertask'], $_POST['hashlist'], $_POST['crackerBinaryVersionId']);
          header("Location: tasks.php");
          die();
        case DSupertaskAction::IMPORT_SUPERTASK:
          AccessControl::getInstance()->checkPermission(DSupertaskAction::IMPORT_SUPERTASK_PERM);
          SupertaskUtils::importSupertask($_POST['name'], $_POST['isCpu'], $_POST['maxAgents'], $_POST['isSmall'], $_POST['optimized'], $_POST['crackerBinaryTypeId'], explode("\n", str_replace("\r\n", "\n", $_POST['masks'])), $_POST['benchtype']);
          break;
        case DSupertaskAction::BULK_SUPERTASK:
          AccessControl::getInstance()->checkPermission(DSupertaskAction::BULK_SUPERTASK_PERM);
          SupertaskUtils::bulkSupertask($_POST['name'], $_POST['command'], $_POST['isCpu'], $_POST['maxAgents'], $_POST['isSmall'], $_POST['crackerBinaryTypeId'], $_POST['benchtype'], @$_POST['basefile'], @$_POST['iterfile'], Login::getInstance()->getUser());
          break;
        case DSupertaskAction::REMOVE_PRETASK_FROM_SUPERTASK:
          AccessControl::getInstance()->checkPermission(DSupertaskAction::REMOVE_PRETASK_FROM_SUPERTASK_PERM);
          SupertaskUtils::removePretaskFromSupertask($_POST['supertaskId'], $_POST['pretaskId']);
          break;
        case DSupertaskAction::ADD_PRETASK_TO_SUPERTASK:
          AccessControl::getInstance()->checkPermission(DSupertaskAction::ADD_PRETASK_TO_SUPERTASK_PERM);
          SupertaskUtils::addPretaskToSupertask($_POST['supertaskId'], $_POST['pretaskId']);
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