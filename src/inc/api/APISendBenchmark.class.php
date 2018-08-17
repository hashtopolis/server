<?php

use DBA\Assignment;
use DBA\QueryFilter;
use DBA\Factory;

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
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());

    $qF1 = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
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
          Factory::getAgentFactory()->update($this->agent);
          $this->sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid benchmark result!");
        }

        // Here we check if the benchmark result would require to split the task and check if the task can be split
        if(SConfig::getInstance()->getVal(DConfig::RULE_SPLIT_DISABLE) == 0 && $split[1] > $task->getChunkTime() * 1000 && $taskWrapper->getTaskType() == DTaskTypes::NORMAL){
          // test if we have a large rule file
          $files = Util::getFileInfo($task, AccessUtils::getAccessGroupsOfAgent($this->agent))[3];
          foreach($files as $file){
            if($file->getFileType() == DFileType::RULE){
              // test if splitting makes sense here
              if(Util::countLines(dirname(__FILE__) . "/../../files/" . $file->getFilename()) > $split[1] / 1000 / $task->getChunkTime() || SConfig::getInstance()->getVal(DConfig::RULE_SPLIT_ALWAYS)){
                // --> split
                TaskUtils::splitByRules($task, $taskWrapper, $files, $file, $split);
                $this->sendErrorResponse(PActions::SEND_BENCHMARK, "Task was split due to benchmark!");
              }
            }
          }
        }

        break;
      case PValuesBenchmarkType::RUN_TIME:
        if (!is_numeric($benchmark) || $benchmark <= 0) {
          $this->agent->setIsActive(0);
          Factory::getAgentFactory()->update($this->agent);
          $this->sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid benchmark result!");
        }
        // normalize time of the benchmark to 100 seconds
        $benchmark = $benchmark / SConfig::getInstance()->getVal(DConfig::BENCHMARK_TIME) * 100;
        break;
      default:
        $this->agent->setIsActive(0);
        Factory::getAgentFactory()->update($this->agent);
        $this->sendErrorResponse(PActions::SEND_BENCHMARK, "Invalid benchmark type!");
    }

    $assignment->setBenchmark($benchmark);
    Factory::getAssignmentFactory()->update($assignment);
    $this->sendResponse(array(
        PResponseSendBenchmark::ACTION => PActions::SEND_BENCHMARK,
        PResponseSendBenchmark::RESPONSE => PValues::SUCCESS,
        PResponseSendBenchmark::BENCHMARK => PValues::OK
      )
    );
  }
}