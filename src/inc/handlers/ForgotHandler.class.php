<?php

class ForgotHandler implements Handler {
  public function __construct($configId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DForgotAction::RESET:
          UserUtils::userForgotPassword($_POST['username'], $_POST['email']);
          UI::addMessage(UI::SUCCESS, "Password reset! You should receive an email soon.");
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