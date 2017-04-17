<?php

use DBA\Chunk;
use DBA\OrderFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::READ_ONLY) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("chunks");
$MENU->setActive("chunks");

$oF = null;
$OBJECTS['all'] = true;
if (!isset($_GET['show'])) {
  $page = 0;
  $PAGESIZE = 50;
  if (isset($_GET['page'])) {
    $page = $_GET['page'];
  }
  $OBJECTS['page'] = $page;
  $numentries = $FACTORIES::getChunkFactory()->countFilter(array());
  $OBJECTS['maxpage'] = floor($numentries / $PAGESIZE);
  $limit = $page * $PAGESIZE;
  $oF = new OrderFilter(Chunk::SOLVE_TIME, "DESC LIMIT $limit, $PAGESIZE");
  $OBJECTS['all'] = false;
}

if ($oF == null) {
  $chunks = $FACTORIES::getChunkFactory()->filter(array());
}
else {
  $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::ORDER => $oF));
}
$spent = new DataSet();
foreach ($chunks as $chunk) {
  $spent->addValue($chunk->getId(), max($chunk->getDispatchTime(), $chunk->getSolveTime()) - $chunk->getDispatchTime());
}
$OBJECTS['chunks'] = $chunks;
$OBJECTS['spent'] = $spent;

$tasks = $FACTORIES::getTaskFactory()->filter(array());
$taskNames = new DataSet();
foreach ($tasks as $task) {
  $taskNames->addValue($task->getId(), $task->getTaskName());
}
$OBJECTS['taskNames'] = $taskNames;

$agents = $FACTORIES::getAgentFactory()->filter(array());
$agentNames = new DataSet();
foreach ($agents as $agent) {
  $agentNames->addValue($agent->getId(), $agent->getAgentName());
}
$OBJECTS['agentNames'] = $agentNames;

echo $TEMPLATE->render($OBJECTS);




