<?php

class ApiHandler implements Handler {
  private $user;

  public function __construct($id = null) {
    // nothing
  }

  public function handle($action) {
    try {
      switch ($action) {
        case DApiAction::DELETE_GROUP:
          ApiUtils::deleteGroup($_POST['groupId']);
          break;
        case DApiAction::EDIT:
          // TODO:
          break;
        case DApiAction::CREATE_GROUP:
          ApiUtils::createGroup($_POST['groupName']);
          break;
        case DApiAction::DELETE_KEY:
          ApiUtils::deleteKey($_POST['keyId']);
          break;
        case DApiAction::CREATE_KEY:
          ApiUtils::createKey($_POST['userId'], $_POST['groupId']);
          break;
        case DApiAction::EDIT_KEY:
          // TODO:
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