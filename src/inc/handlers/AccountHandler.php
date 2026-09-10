<?php

namespace Hashtopolis\inc\handlers;

use Hashtopolis\inc\utils\AccessControl;
use Hashtopolis\inc\utils\AccountUtils;
use Throwable;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\defines\DAccountAction;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\UI;

class AccountHandler implements Handler {
  private $user;
  
  public function __construct($userId = null) {
    if ($userId == null) {
      $this->user = null;
      return;
    }
    
    $this->user = Factory::getUserFactory()->get($userId);
    if ($this->user == null) {
      UI::printError("FATAL", "User with ID $userId not found!");
    }
  }
  
  public function handle(string $action): void {
    try {
      switch ($action) {
        case DAccountAction::SET_EMAIL:
          AccessControl::getInstance()->checkPermission(DAccountAction::SET_EMAIL_PERM);
          AccountUtils::setEmail($_POST['email'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Email updated successfully!");
          break;
        case DAccountAction::UPDATE_LIFETIME:
          AccessControl::getInstance()->checkPermission(DAccountAction::UPDATE_LIFETIME_PERM);
          AccountUtils::updateSessionLifetime($_POST['lifetime'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Updated session lifetime successfully!");
          break;
        case DAccountAction::CHANGE_PASSWORD:
          AccessControl::getInstance()->checkPermission(DAccountAction::CHANGE_PASSWORD_PERM);
          AccountUtils::changePassword($_POST['oldpass'], $_POST['newpass'], $_POST['reppass'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Password was updated successfully!");
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (Throwable $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
    
    UI::add('user', Login::getInstance()->getUser());
  }
}