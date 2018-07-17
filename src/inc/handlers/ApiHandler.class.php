<?php

class ApiHandler implements Handler {
  private $user;

  public function __construct($id = null){
    // nothing
  }
  
  public function handle($action) {
    try{
      switch ($action) {
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (HTException $e){
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
}