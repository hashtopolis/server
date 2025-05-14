<?php

class AgentBinaryHandler implements Handler {
  
  public function __construct($id = null) {
    //nothing
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DAgentBinaryAction::NEW_BINARY:
          AccessControl::getInstance()->checkPermission(DAgentBinaryAction::NEW_BINARY_PERM);
          AgentBinaryUtils::newBinary($_POST['type'], $_POST['os'], $_POST['filename'], $_POST['version'], $_POST['updateTrack'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Binary was added successfully!");
          break;
        case DAgentBinaryAction::EDIT_BINARY:
          AccessControl::getInstance()->checkPermission(DAgentBinaryAction::EDIT_BINARY_PERM);
          AgentBinaryUtils::editBinary($_POST['id'], $_POST['type'], $_POST['os'], $_POST['filename'], $_POST['version'], $_POST['updateTrack'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Binary was updated successfully!");
          break;
        case DAgentBinaryAction::DELETE_BINARY:
          AccessControl::getInstance()->checkPermission(DAgentBinaryAction::DELETE_BINARY_PERM);
          AgentBinaryUtils::deleteBinary($_POST['id']);
          UI::addMessage(UI::SUCCESS, "Binary deleted successfully!");
          break;
        case DAgentBinaryAction::CHECK_UPDATE:
          AccessControl::getInstance()->checkPermission(DAgentBinaryAction::CHECK_UPDATE_PERM);
          if (AgentBinaryUtils::checkUpdate($_POST['binaryId'])) {
            UI::addMessage(UI::SUCCESS, "New update is available!");
          }
          else {
            UI::addMessage(UI::WARN, "No update available!");
          }
          break;
        case DAgentBinaryAction::UPGRADE_BINARY:
          AccessControl::getInstance()->checkPermission(DAgentBinaryAction::UPGRADE_BINARY_PERM);
          AgentBinaryUtils::executeUpgrade($_POST['binaryId']);
          UI::addMessage(UI::SUCCESS, "Agent binary was upgraded!");
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