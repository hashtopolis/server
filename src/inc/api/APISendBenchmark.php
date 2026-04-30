<?php

namespace Hashtopolis\inc\api;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuerySendBenchmark;
use Hashtopolis\inc\agent\PResponseSendBenchmark;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\agent\PValuesBenchmarkType;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\SConfig;

class APISendBenchmark extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQuerySendBenchmark::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid benchmark query!");
    }
    $this->checkToken(PActions::SEND_BENCHMARK, $QUERY);
    $this->updateAgent(PActions::SEND_BENCHMARK);
    
    $task = Factory::getTaskFactory()->get($QUERY[PQuerySendBenchmark::TASK_ID]);
    if ($task == null) {
      $this->sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid task ID!");
    }
    
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($assignment == null) {
      $this->sendErrorResponse(PActions::SEND_BENCHMARK, "You are not assigned to this task!");
    }
    
    $type = $QUERY[PQuerySendBenchmark::TYPE];
    $benchmark = $QUERY[PQuerySendBenchmark::RESULT];
    
    DServerLog::log(DServerLog::TRACE, "Agent sending benchmark", [$this->agent, $task, $type, $benchmark]);
    
    switch ($type) {
      case PValuesBenchmarkType::SPEED_TEST:
        $split = explode(":", $benchmark);
        if (sizeof($split) != 2 || !is_numeric($split[0]) || !is_numeric($split[1]) || $split[0] <= 0 || $split[1] <= 0) {
          Factory::getAgentFactory()->set($this->agent, Agent::IS_ACTIVE, 0);
          DServerLog::log(DServerLog::ERROR, "Invalid speed test benchmark result!", [$this->agent, $benchmark]);
          $this->sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid benchmark result!");
        }
        break;
      case PValuesBenchmarkType::RUN_TIME:
        if (!is_numeric($benchmark) || $benchmark <= 0) {
          Factory::getAgentFactory()->set($this->agent, Agent::IS_ACTIVE, 0);
          DServerLog::log(DServerLog::ERROR, "Invalid benchmark results for runtime benchmark", [$this->agent, $task, $benchmark]);
          $this->sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid benchmark result!");
        }
        // normalize time of the benchmark to 100 seconds
        $benchmark = $benchmark / SConfig::getInstance()->getVal(DConfig::BENCHMARK_TIME) * 100;
        DServerLog::log(DServerLog::TRACE, "Saving normalized runtime benchmark", [$this->agent, $task, $benchmark]);
        break;
      default:
        Factory::getAgentFactory()->set($this->agent, Agent::IS_ACTIVE, 0);
        $this->sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid benchmark type!");
    }
    
    $assignment->setBenchmark($benchmark);
    Factory::getAssignmentFactory()->update($assignment);
    DServerLog::log(DServerLog::DEBUG, "Saved agent benchmark", [$this->agent, $task, $assignment]);
    $this->sendResponse(array(
        PResponseSendBenchmark::ACTION => PActions::SEND_BENCHMARK,
        PResponseSendBenchmark::RESPONSE => PValues::SUCCESS,
        PResponseSendBenchmark::BENCHMARK => PValues::OK
      )
    );
  }
}