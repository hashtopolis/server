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
          AgentBinaryUtils::newBinary($_POST['type'], $_POST['os'], $_POST['filename'], $_POST['version'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Binary was added successfully!");
          break;
        case DAgentBinaryAction::EDIT_BINARY:
          AccessControl::getInstance()->checkPermission(DAgentBinaryAction::EDIT_BINARY_PERM);
          AgentBinaryUtils::editBinary($_POST['id'], $_POST['type'], $_POST['os'], $_POST['filename'], $_POST['version'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Binary was updated successfully!");
          break;
        case DAgentBinaryAction::DELETE_BINARY:
          AccessControl::getInstance()->checkPermission(DAgentBinaryAction::DELETE_BINARY_PERM);
          AgentBinaryUtils::deleteBinary($_POST['id']);
          UI::addMessage(UI::SUCCESS, "Binary deleted successfully!");
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