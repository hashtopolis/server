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
$message = "";

//catch actions here...
if (isset($_POST['action'])) {
  $agentHandler = new AgentHandler($_POST['agentId']);
  $agentHandler->handle($_POST['action']);
  Util::refresh();
}

$allTasks = $FACTORIES::getTaskFactory()->filter(array());

//TODO: put code for new agent here
if (isset($_GET['id'])) {
  //show agent detail
  $TEMPLATE = new Template("agents/detail");
  $agent = $FACTORIES::getAgentFactory()->get(intval($_GET['id']));
  if (!$agent) {
    $message = "<div class='alert alert-danger'>Agent not found!</div>";
  }
  else {
    $users = $FACTORIES::getUserFactory()->filter(array());
    $OBJECTS['users'] = $users;
    $OBJECTS['agent'] = $agent;
    
    //TODO: not done yet
    /*$res = $DB->query("SELECT agents.*,assignments.task,SUM(GREATEST(chunks.solvetime,chunks.dispatchtime)-chunks.dispatchtime) AS spent FROM agents LEFT JOIN assignments ON assignments.agent=agents.id LEFT JOIN chunks ON chunks.agent=agents.id WHERE agents.id=" . $agent->getId());
    $agentSet = new DataSet();
    $agentSet->setValues($res->fetch());
    
    $res = $DB->query("SELECT errors.*,chunks.id FROM errors LEFT JOIN chunks ON (errors.time BETWEEN chunks.dispatchtime AND chunks.solvetime) AND chunks.agent=errors.agent WHERE errors.agent=" . $agent->getId() . " ORDER BY time DESC");
    $res = $res->fetchAll();
    $errors = array();
    foreach ($res as $error) {
      $set = new DataSet();
      $set->setValues($error);
      $errors[] = $set;
    }
    
    $res = $DB->query("SELECT chunks.*,GREATEST(chunks.dispatchtime,chunks.solvetime)-chunks.dispatchtime AS spent,tasks.name AS taskname FROM chunks JOIN tasks ON chunks.task=tasks.id WHERE agent=" . $agent->getId() . " ORDER BY chunks.dispatchtime DESC,chunks.skip DESC LIMIT 100");
    $res = $res->fetchAll();
    $chunks = array();
    foreach ($res as $chunk) {
      $set = new DataSet();
      $set->setValues($chunk);
      $chunks[] = $set;
    }
    
    $platforms = array();
    $plt = Util::getStaticArray('-1', 'platforms');
    foreach ($plt as $key => $platform) {
      $set = new DataSet();
      $set->addValue('key', $key);
      $set->addValue('val', $platform);
      $platforms[] = $set;
    }
    $OBJECTS['platforms'] = $platforms;
    $OBJECTS['agent'] = $agentSet;
    $OBJECTS['errors'] = $errors;
    $OBJECTS['chunks'] = $chunks;*/
    //TODO: until here
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
      $chunks = $FACTORIES::getChunkFactory()->filter();
      foreach ($chunks as $chunk) {
        if (max($chunk->getDispatchTime(), $chunk->getSolveTime()) > time() - $CONFIG->getVal('chunktimeout')) {
          $isWorking = 1;
          $taskId = $assignment->getTaskId();
        }
      }
    }
    $set->addValue("isWorking", $isWorking);
    $set->addValue("taskId", $taskId);
    $allAgents[] = $set;
  }
  $OBJECTS['numAgents'] = sizeof($allAgents);
  $OBJECTS['sets'] = $allAgents;
}

$OBJECTS['allTasks'] = $allTasks;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




