<?php

use DBA\Factory;

class BenchmarkHandler implements Handler {
  private $agent;
  
  public function __construct($agentId = null) {
    if ($agentId == null) {
      $this->agent = null;
      return;
    }
    
    $this->agent = Factory::getAgentFactory()->get($agentId);
    if ($this->agent == null) {
      UI::printError("FATAL", "Agent with ID $agentId not found!");
    }
  }

  public function handle($action) {
    try {
      switch ($action) {
        case DBenchmarkAction::DELETE_BENCHMARK:
          AccessControl::getInstance()->checkPermission(DBenchmarkAction::DELETE_BENCHMARK);
          $benchId = $_POST['benchmarkId'];
          BenchmarkUtils::delete($_POST['benchmarkId']);
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