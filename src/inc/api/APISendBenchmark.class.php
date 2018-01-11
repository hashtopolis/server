<?php

use DBA\Assignment;
use DBA\QueryFilter;

class APISendBenchmark extends APIBasic {
  public function execute($QUERY = array()) {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
    
    if (!PQuerySendBenchmark::isValid($QUERY)) {
      API::sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid benchmark query!");
    }
    $this->checkToken(PActions::SEND_BENCHMARK, $QUERY);
    $this->updateAgent(PActions::SEND_BENCHMARK);
    
    $task = $FACTORIES::getTaskFactory()->get($QUERY[PQuerySendBenchmark::TASK_ID]);
    if ($task == null) {
      $this->sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid task ID!");
    }
    
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($assignment == null) {
      $this->sendErrorResponse(PActions::SEND_BENCHMARK, "You are not assigned to this task!");
    }
    
    $type = $QUERY[PQuerySendBenchmark::TYPE];
    $benchmark = $QUERY[PQuerySendBenchmark::RESULT];
    
    switch ($type) {
      case PValuesBenchmarkType::SPEED_TEST:
        $split = explode(":", $benchmark);
        if (sizeof($split) != 2 || !is_numeric($split[0]) || !is_numeric($split[1]) || $split[0] <= 0 || $split[1] <= 0) {
          $this->agent->setIsActive(0);
          $FACTORIES::getAgentFactory()->update($this->agent);
          API::sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid benchmark result!");
        }
        break;
      case PValuesBenchmarkType::RUN_TIME:
        if (!is_numeric($benchmark) || $benchmark <= 0) {
          $this->agent->setIsActive(0);
          $FACTORIES::getAgentFactory()->update($this->agent);
          API::sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid benchmark result!");
        }
        // normalize time of the benchmark to 100 seconds
        $benchmark = $benchmark / $CONFIG->getVal(DConfig::BENCHMARK_TIME) * 100;
        break;
      default:
        $this->agent->setIsActive(0);
        $FACTORIES::getAgentFactory()->update($this->agent);
        API::sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid benchmark type!");
    }
    
    $assignment->setBenchmark($benchmark);
    $FACTORIES::getAssignmentFactory()->update($assignment);
    $this->sendResponse(array(
        PResponseSendBenchmark::ACTION => PActions::SEND_BENCHMARK,
        PResponseSendBenchmark::RESPONSE => PValues::SUCCESS,
        PResponseSendBenchmark::BENCHMARK => PValues::OK
      )
    );
  }
}