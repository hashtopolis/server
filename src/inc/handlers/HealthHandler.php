<?php

namespace Hashtopolis\inc\handlers;

use Hashtopolis\inc\utils\AccessControl;
use Exception;
use Hashtopolis\inc\defines\DHealthCheckAction;
use Hashtopolis\inc\utils\HealthUtils;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\UI;

class HealthHandler implements Handler {
  public function __construct($userId = null) {
    //nothing to do
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DHealthCheckAction::CREATE:
          AccessControl::getInstance()->checkPermission(DHealthCheckAction::CREATE_PERM);
          HealthUtils::createHealthCheck(intval($_POST['hashtypeId']), intval($_POST['type']), intval($_POST['crackerBinaryVersionId']));
          break;
        case DHealthCheckAction::RESET_AGENT:
          AccessControl::getInstance()->checkPermission(DHealthCheckAction::RESET_AGENT_PERM);
          HealthUtils::resetAgentCheck(intval($_POST['healthCheckAgentId']));
          break;
        case DHealthCheckAction::DELETE_HEALTH_CHECK:
          AccessControl::getInstance()->checkPermission(DHealthCheckAction::DELETE_HEALTH_CHECK_PERM);
          HealthUtils::deleteHealthCheck(intval($_POST['healthCheckId']));
          break;
        default:
          throw new HTException("Invalid action!");
      }
    }
    catch (Exception $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
}