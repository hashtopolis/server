<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 20) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("agents/index");
$MENU->setActive("agents_list");

//catch actions here...
if (isset($_POST['action'])) {
  $agentHandler = new AgentHandler($_POST['agentId']);
  $agentHandler->handle($_POST['action']);
  Util::refresh();
}

$allTasks = $FACTORIES::getTaskFactory()->filter(array());

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
    
    $qF = new QueryFilter("agentId", $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => $qF), true);
    $currentTask = 0;
    if($assignment != null){
      $currentTask = $assignment->getTaskId();
    }
    $OBJECTS['currentTask'] = $currentTask;
    
    $qF = new QueryFilter("agentId", $agent->getId(), "=");
    $OBJECTS['errors'] = $FACTORIES::getAgentErrorFactory()->filter(array('filter' => $qF));
    
    $qF = new QueryFilter("agentId", $agent->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF));
    $timeSpent = 0;
    foreach($chunks as $chunk){
      $timeSpent += max($chunk->getSoleTime(), $chunk->getDispatchTime()) - $chunk->getDispatchTime();
    }
    $OBJECTS['chunks'] = $chunks;
    $OBJECTS['timeSpent'] = $timeSpent;
  }
}
else {
  $oF = new OrderFilter("agentId", "ASC");
  $agents = $FACTORIES::getAgentFactory()->filter(array('order' => array($oF)));
  $allAgents = array();
  foreach ($agents as $agent) {
    $set = new DataSet();
    $agent->setGpus(explode("\n", $agent->getGpus()));
    $set->addValue("agent", $agent);
    
    $qF = new QueryFilter("agentId", $agent->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF)));
    $isWorking = 0;
    $taskId = 0;
    if (sizeof($assignments) > 0) {
      $assignment = $assignments[0];
      $qF = new QueryFilter("taskId", $assignment->getTaskId(), "=");
      $chunks = $FACTORIES::getChunkFactory()->filter(array());
      foreach ($chunks as $chunk) {
        if (max($chunk->getDispatchTime(), $chunk->getSolveTime()) > time() - $CONFIG->getVal('chunktimeout')) {
          $isWorking = 1;
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




