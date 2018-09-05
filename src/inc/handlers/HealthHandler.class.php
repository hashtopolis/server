<?php

class HealthHandler implements Handler {
  public function __construct($userId = null) {
    //nothing to do
  }

  public function handle($action) {
    try {
      switch ($action) {
        case DHealthCheckAction::CREATE:
          AccessControl::getInstance()->checkPermission(DHealthCheckAction::CREATE);
          HealthUtils::createHealthCheck($_POST['hashtypeId'], $_POST['type'], $_POST['crackerBinaryVersionId']);
          break;
        default:
          throw new HTException("Invalid action!");
      }
    }
    catch (HTException $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
}