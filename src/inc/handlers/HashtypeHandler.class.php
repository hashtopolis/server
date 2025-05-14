<?php

class HashtypeHandler implements Handler {
  public function __construct($hashtypeId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DHashtypeAction::DELETE_HASHTYPE:
          HashtypeUtils::deleteHashtype($_POST['type']);
          UI::addMessage(UI::SUCCESS, "Hashtype was deleted successfully!");
          break;
        case DHashtypeAction::ADD_HASHTYPE:
          HashtypeUtils::addHashtype($_POST['id'], $_POST['description'], $_POST['isSalted'], $_POST['isSlowHash'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "New hashtype created successfully!");
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