<?php

class DevelopmentHandler implements Handler {
  public function __construct($hashcatId = null) {
    //nothing
  }
  
  public function handle($action) {
    try {
      switch ($action) {
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