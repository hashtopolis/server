<?php

class DevelopmentHandler implements Handler {
  public function __construct($hashcatId = null) {
    //nothing
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DSetupAction::EXECUTE_SETUP:
          AccessControl::getInstance()->checkPermission(DSetupAction::EXECUTE_SETUP_PERM);
          DevelopmentUtils::runSetup($_POST['identifier']);
          UI::addMessage(UI::SUCCESS, "Tool was executed successfully!");
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