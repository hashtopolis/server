<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 5) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("chunks");
$MENU->setActive("chunks");

$chunks = $FACTORIES::getChunkFactory()->filter(array());
$spent = new DataSet();
foreach($chunks as $chunk){
  $spent->addValue($chunk->getId(), max($chunk->getDispatchTime(), $chunk->getSolveTime()) - $chunk->getDispatchTime());
}
$OBJECTS['chunks'] = $chunks;
$OBJECTS['spent'] = $spent;

$tasks = $FACTORIES::getTaskFactory()->filter(array());
$taskNames = new DataSet();
foreach($tasks as $task){
  $taskNames->addValue($task->getId(), $task->getTaskName());
}
$OBJECTS['taskNames'] = $taskNames;

$agents = $FACTORIES::getAgentFactory()->filter(array());
$agentNames = new DataSet();
foreach($agents as $agent){
  $agentNames->addValue($agent->getId(), $agent->getAgentName());
}
$OBJECTS['agentNames'] = $agentNames;

echo $TEMPLATE->render($OBJECTS);




