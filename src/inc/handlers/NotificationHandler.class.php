<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 10.11.16
 * Time: 14:38
 */


class NotificationHandler implements Handler {
  
  public function __construct($id = null) {
    // nothing required here
  }
  
  public function handle($action) {
    switch ($action) {
      //TODO: add handling
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
}