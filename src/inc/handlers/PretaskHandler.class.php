<?php

class PretaskHandler implements Handler {
  public function __construct($fileId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DPretaskAction::DELETE_PRETASK:
          AccessControl::getInstance()->checkPermission(DPretaskAction::DELETE_PRETASK_PERM);
          PretaskUtils::deletePretask($_POST['pretaskId']);
          header("Location: pretasks.php");
          die();
        case DPretaskAction::RENAME_PRETASK:
          AccessControl::getInstance()->checkPermission(DPretaskAction::RENAME_PRETASK_PERM);
          PretaskUtils::renamePretask($_POST['pretaskId'], $_POST['name']);
          break;
        case DPretaskAction::SET_TIME:
          AccessControl::getInstance()->checkPermission(DPretaskAction::SET_TIME_PERM);
          PretaskUtils::setChunkTime($_POST['pretaskId'], $_POST['chunktime']);
          break;
        case DPretaskAction::SET_COLOR:
          AccessControl::getInstance()->checkPermission(DPretaskAction::SET_COLOR_PERM);
          PretaskUtils::setColor($_POST['pretaskId'], $_POST['color']);
          break;
        case DPretaskAction::SET_PRIORITY:
          AccessControl::getInstance()->checkPermission(DPretaskAction::SET_PRIORITY_PERM);
          PretaskUtils::setPriority($_POST['pretaskId'], $_POST['priority']);
          if (isset($_GET['super'])) {
            header("Location: supertasks.php");
            die();
          }
          break;
        case DPretaskAction::SET_MAX_AGENTS:
          AccessControl::getInstance()->checkPermission(DPretaskAction::SET_MAX_AGENTS_PERM);
          PretaskUtils::setMaxAgents($_POST['pretaskId'], $_POST['maxAgents']);
          break;
        case DPretaskAction::SET_CPU_TASK:
          AccessControl::getInstance()->checkPermission(DPretaskAction::SET_CPU_TASK_PERM);
          PretaskUtils::setCpuOnlyTask($_POST['pretaskId'], $_POST['isCpu']);
          break;
        case DPretaskAction::SET_SMALL_TASK:
          AccessControl::getInstance()->checkPermission(DPretaskAction::SET_SMALL_TASK_PERM);
          PretaskUtils::setSmallTask($_POST['pretaskId'], $_POST['isSmall']);
          break;
        case DPretaskAction::CREATE_TASK:
          AccessControl::getInstance()->checkPermission(DPretaskAction::CREATE_TASK_PERM);
          PretaskUtils::createPretask($_POST['name'], $_POST['cmdline'], $_POST['chunk'], $_POST['status'], $_POST['color'], $_POST['cpuOnly'], $_POST['isSmall'], $_POST['benchType'], $_POST['adfile'], $_POST['crackerBinaryTypeId'], $_POST['maxAgents']);
          header("Location: pretasks.php");
          die();
        case DPretaskAction::CHANGE_ATTACK:
          AccessControl::getInstance()->checkPermission(DPretaskAction::CHANGE_ATTACK_PERM);
          PretaskUtils::changeAttack($_POST['pretaskId'], $_POST['attackCmd']);
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