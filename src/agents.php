<?php

use DBA\Agent;
use DBA\AgentError;
use DBA\Assignment;
use DBA\Chunk;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */
/** @var DataSet $CONFIG */

if (isset($_GET['download'])) {
  $binaryId = $_GET['download'];
  $agentHandler = new AgentHandler();
  $agentHandler->handle('downloadagent');
}

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::USER) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("agents/index");
$MENU->setActive("agents_list");

//catch actions here...
if (isset($_POST['action']) && Util::checkCSRF($_POST['csrf'])) {
  $binaryId = @$_POST['binary'];
  $agentHandler = new AgentHandler($_POST['agentId']);
  $agentHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$qF = new QueryFilter(Task::HASHLIST_ID, null, "<>");
$allTasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF));

if (isset($_GET['id'])) {
  //show agent detail
  $TEMPLATE = new Template("agents/detail");
  $agent = $FACTORIES::getAgentFactory()->get($_GET['id']);
  if (!$agent) {
    UI::printError("ERROR", "Agent not found!");
  }
  else {
    $OBJECTS['agent'] = $agent;
    $OBJECTS['users'] = $FACTORIES::getUserFactory()->filter(array());
    $OBJECTS['allTasks'] = $FACTORIES::getTaskFactory()->filter(array());
    
    $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    $currentTask = 0;
    if ($assignment != null) {
      $currentTask = $assignment->getTaskId();
    }
    $OBJECTS['currentTask'] = $currentTask;
    
    $qF = new QueryFilter(AgentError::AGENT_ID, $agent->getId(), "=");
    $OBJECTS['errors'] = $FACTORIES::getAgentErrorFactory()->filter(array($FACTORIES::FILTER => $qF));
    
    $qF = new QueryFilter(Chunk::AGENT_ID, $agent->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $timeSpent = 0;
    foreach ($chunks as $chunk) {
      $timeSpent += max($chunk->getSolveTime(), $chunk->getDispatchTime()) - $chunk->getDispatchTime();
    }
    $OBJECTS['chunks'] = $chunks;
    $OBJECTS['timeSpent'] = $timeSpent;
  }
}
else if (isset($_GET['new']) && $LOGIN->getLevel() >= DAccessLevel::SUPERUSER) {
  $MENU->setActive("agents_new");
  $TEMPLATE = new Template("agents/new");
  $vouchers = $FACTORIES::getRegVoucherFactory()->filter(array());
  $OBJECTS['vouchers'] = $vouchers;
  $binaries = $FACTORIES::getAgentBinaryFactory()->filter(array());
  $OBJECTS['agentBinaries'] = $binaries;
  
  $url = explode("/", $_SERVER['PHP_SELF']);
  unset($url[sizeof($url) - 1]);
  $OBJECTS['apiUrl'] = Util::buildServerUrl() . implode("/", $url) . "/api/server.php";
  $OBJECTS['agentUrl'] = Util::buildServerUrl() . implode("/", $url) . "/agents.php?download=";
}
else {
  $oF = new OrderFilter(Agent::AGENT_ID, "ASC");
  $agents = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::ORDER => array($oF)));
  $allAgents = array();
  foreach ($agents as $agent) {
    $set = new DataSet();
    $agent->setGpus(explode("\n", $agent->getGpus()));
    $set->addValue("agent", $agent);
    
    $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF)));
    $isWorking = 0;
    $taskId = 0;
    if (sizeof($assignments) > 0) {
      $assignment = $assignments[0];
      $qF = new QueryFilter(Chunk::TASK_ID, $assignment->getTaskId(), "=");
      $chunks = $FACTORIES::getChunkFactory()->filter(array());
      foreach ($chunks as $chunk) {
        if (max($chunk->getDispatchTime(), $chunk->getSolveTime()) > time() - $CONFIG->getVal(DConfig::CHUNK_TIMEOUT) && $chunk->getAgentId() == $agent->getId()) {
          $isWorking = 1;
          $set->addValue("speed", $chunk->getSpeed());
        }
      }
      $taskId = $assignment->getTaskId();
    }
    $set->addValue("isWorking", $isWorking);
    $set->addValue("taskId", $taskId);
    $allAgents[] = $set;
  }
  $OBJECTS['numAgents'] = sizeof($allAgents);
  $OBJECTS['sets'] = $allAgents;
}

$OBJECTS['allTasks'] = $allTasks;

echo $TEMPLATE->render($OBJECTS);




