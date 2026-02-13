<?php

namespace Hashtopolis\inc\handlers;

use Hashtopolis\inc\utils\ApiUtils;
use Exception;
use Hashtopolis\inc\defines\DApiAction;
use Hashtopolis\inc\UI;

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
          UI::addMessage(UI::SUCCESS, "API group was deleted!");
          break;
        case DApiAction::EDIT:
          ApiUtils::update($_POST['groupId'], $_POST['perm'], $_POST['section']);
          UI::addMessage(UI::SUCCESS, "Saved changes!");
          break;
        case DApiAction::CREATE_GROUP:
          ApiUtils::createGroup($_POST['groupName']);
          header("Location: api.php");
          die();
        case DApiAction::DELETE_KEY:
          ApiUtils::deleteKey($_POST['keyId']);
          UI::addMessage(UI::SUCCESS, "API key was deleted!");
          break;
        case DApiAction::CREATE_KEY:
          ApiUtils::createKey($_POST['userId'], $_POST['groupId']);
          header("Location: api.php");
          die();
        case DApiAction::EDIT_KEY:
          ApiUtils::editKey($_POST['keyId'], $_POST['userId'], $_POST['groupId'], $_POST['startValid'], $_POST['endValid']);
          UI::addMessage(UI::SUCCESS, "Saved changes!");
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