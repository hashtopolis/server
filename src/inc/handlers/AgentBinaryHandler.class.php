<?php

class AgentBinaryHandler implements Handler {
  
  public function __construct($id = null) {
    //nothing
  }
  
  public function handle($action) {
    /** @var $LOGIN Login */
    global $ACCESS_CONTROL, $LOGIN;
    
    try {
      switch ($action) {
        case DAgentBinaryAction::NEW_BINARY:
          $ACCESS_CONTROL->checkPermission(DAgentBinaryAction::NEW_BINARY_PERM);
          AgentBinaryUtils::newBinary($_POST['type'], $_POST['os'], $_POST['filename'], $_POST['version'], $LOGIN->getUser());
          UI::addMessage(UI::SUCCESS, "Binary was added successfully!");
          break;
        case DAgentBinaryAction::EDIT_BINARY:
          $ACCESS_CONTROL->checkPermission(DAgentBinaryAction::EDIT_BINARY_PERM);
          AgentBinaryUtils::editBinary($_POST['id'], $_POST['type'], $_POST['os'], $_POST['filename'], $_POST['version'], $LOGIN->getUser());
          UI::addMessage(UI::SUCCESS, "Binary was updated successfully!");
          break;
        case DAgentBinaryAction::DELETE_BINARY:
          $ACCESS_CONTROL->checkPermission(DAgentBinaryAction::DELETE_BINARY_PERM);
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