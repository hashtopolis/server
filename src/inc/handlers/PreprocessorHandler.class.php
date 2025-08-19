<?php

class PreprocessorHandler implements Handler {
  public function __construct($hashcatId = null) {
    //nothing
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DPreprocessorAction::ADD_PREPROCESSOR:
          AccessControl::getInstance()->checkPermission(DPreprocessorAction::ADD_PREPROCESSOR_PERM);
          PreprocessorUtils::addPreprocessor($_POST['name'], $_POST['binaryName'], $_POST['url'], $_POST['keyspaceCommand'], $_POST['skipCommand'], $_POST['limitCommand']);
          UI::addMessage(UI::SUCCESS, "Added new preprocessor!");
          break;
        case DPreprocessorAction::DELETE_PREPROCESSOR:
          AccessControl::getInstance()->checkPermission(DPreprocessorAction::DELETE_PREPROCESSOR_PERM);
          PreprocessorUtils::delete($_POST['preprocessorId']);
          UI::addMessage(UI::SUCCESS, "Deleted preprocessor successfully!");
          break;
        case DPreprocessorAction::EDIT_PREPROCESSOR:
          AccessControl::getInstance()->checkPermission(DPreprocessorAction::EDIT_PREPROCESSOR_PERM);
          PreprocessorUtils::editPreprocessor($_POST['preprocessorId'], $_POST['name'], $_POST['binaryName'], $_POST['url'], $_POST['keyspaceCommand'], $_POST['skipCommand'], $_POST['limitCommand']);
          UI::addMessage(UI::SUCCESS, "Saved changes successfully!");
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