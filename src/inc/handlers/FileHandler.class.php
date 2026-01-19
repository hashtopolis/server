<?php

class FileHandler implements Handler {
  public function __construct($fileId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DFileAction::DELETE_FILE:
          AccessControl::getInstance()->checkPermission(DFileAction::DELETE_FILE_PERM);
          FileUtils::delete($_POST['file'], AccessControl::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Successfully deleted file!");
          break;
        case DFileAction::SET_SECRET:
          AccessControl::getInstance()->checkPermission(DFileAction::SET_SECRET_PERM);
          FileUtils::switchSecret($_POST['file'], $_POST["secret"], AccessControl::getInstance()->getUser());
          break;
        case DFileAction::ADD_FILE:
          AccessControl::getInstance()->checkPermission(DFileAction::ADD_FILE_PERM);
          $fileCount = FileUtils::add($_POST['source'], $_FILES, $_POST, @$_GET['view']);
          UI::addMessage(UI::SUCCESS, "Successfully added $fileCount files!");
          break;
        case DFileAction::EDIT_FILE:
          AccessControl::getInstance()->checkPermission(DFileAction::EDIT_FILE_PERM);
          FileUtils::saveChanges($_POST['fileId'], $_POST['filename'], $_POST['accessGroupId'], AccessControl::getInstance()->getUser());
          FileUtils::setFileType($_POST['fileId'], $_POST['filetype'], AccessControl::getInstance()->getUser());
          break;
        case DFileAction::COUNT_FILE_LINES:
          AccessControl::getInstance()->checkPermission(DFileAction::COUNT_FILE_LINES_PERM);
          FileUtils::fileCountLines($_POST['file']);
          UI::addMessage(UI::SUCCESS, "Line count has been successfully calculated!");
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