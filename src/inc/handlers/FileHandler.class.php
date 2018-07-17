<?php

class FileHandler implements Handler {
  public function __construct($fileId = null) {
    //we need nothing to load
  }

  public function handle($action) {
    global $ACCESS_CONTROL;

    try{
      switch ($action) {
        case DFileAction::DELETE_FILE:
          $ACCESS_CONTROL->checkPermission(DFileAction::DELETE_FILE_PERM);
          FileUtils::delete($_POST['file']);
          UI::addMessage(UI::SUCCESS, "Successfully deleted file!");
          break;
        case DFileAction::SET_SECRET:
          $ACCESS_CONTROL->checkPermission(DFileAction::SET_SECRET_PERM);
          FileUtils::switchSecret($_POST['file'], $_POST["secret"]);
          break;
        case DFileAction::ADD_FILE:
          $ACCESS_CONTROL->checkPermission(DFileAction::ADD_FILE_PERM);
          $fileCount = FileUtils::add($_POST['source'], $_FILES, $_POST, @$_GET['view']);
          UI::addMessage(UI::SUCCESS, "Successfully added $fileCount files!");
          break;
        case DFileAction::EDIT_FILE:
          $ACCESS_CONTROL->checkPermission(DFileAction::EDIT_FILE_PERM);
          FileUtils::saveChanges($_POST['fileId'], $_POST['filename']);
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch(HTException $e){
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
}