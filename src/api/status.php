<?php

use DBA\Agent;
use DBA\Assignment;
use DBA\Chunk;
use DBA\QueryFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/../inc/load.php");

$answer = array();

$qF = new QueryFilter(Agent::LAST_TIME, time() - SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT), ">");
$onlineAgents = Factory::getAgentFactory()->filter(array(Factory::FILTER => $qF));

$answer[DStats::AGENTS_ONLINE] = sizeof($onlineAgents);

$activeAgents = array();
$totalSpeed = 0;
foreach ($onlineAgents as $agent) {
  $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
  $assignments = $FACTORIES::getAssignmentFactory()->filter(array(Factory::FILTER => array($qF)));
  if (sizeof($assignments) > 0) {
    $assignment = $assignments[0];
    $qF = new QueryFilter(Chunk::TASK_ID, $assignment->getTaskId(), "=");
    $chunks = Factory::getChunkFactory()->filter(array());
    foreach ($chunks as $chunk) {
      if (max($chunk->getDispatchTime(), $chunk->getSolveTime()) > time() - SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT) && $chunk->getAgentId() == $agent->getId()) {
        $activeAgents[] = $agent;
        $totalSpeed += $chunk->getSpeed();
        break;
      }
    }
  }
}

$answer[DStats::AGENTS_ACTIVE] = sizeof($activeAgents);
$answer[DStats::AGENTS_TOTAL_SPEED] = $totalSpeed;

$tasks = Factory::getTaskFactory()->filter(array());
$answer[DStats::TASKS_TOTAL] = sizeof($tasks);
$finishedTasks = array();
$runningTasks = array();
$queuedTasks = array();
foreach ($tasks as $task) {
  if ($task->getKeyspace() > 0 && $task->getKeyspace() == $task->getKeyspaceProgress()) {
    $finishedTasks[] = $task;
  }
  else if ($task->getKeyspace() > 0) {
    $runningTasks[] = $task;
  }
  else {
    $queuedTasks[] = $task;
  }
}
$answer[DStats::TASKS_FINISHED] = sizeof($finishedTasks);
$answer[DStats::TASKS_RUNNING] = sizeof($runningTasks);
$answer[DStats::TASKS_QUEUED] = sizeof($queuedTasks);

header("Content-Type: application/json");
echo json_encode($answer);
