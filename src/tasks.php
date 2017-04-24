<?php
use DBA\Agent;
use DBA\Assignment;
use DBA\Chunk;
use DBA\File;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\TaskFile;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */
/** @var DataSet $CONFIG */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$TEMPLATE = new Template("tasks/index");
$MENU->setActive("tasks_list");

//catch agents actions here...
if (isset($_POST['action'])) {
  $taskHandler = new TaskHandler();
  $taskHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

//test if auto-reload is enabled
$autorefresh = 0;
if (isset($_COOKIE['autorefresh']) && $_COOKIE['autorefresh'] == '1') {
  $autorefresh = 10;
}
if (isset($_POST['toggleautorefresh'])) {
  if ($autorefresh != 0) {
    $autorefresh = 0;
    setcookie("autorefresh", "0", time() - 600);
  }
  else {
    $autorefresh = 10;
    setcookie("autorefresh", "1", time() + 3600 * 24);
  }
  Util::refresh();
}
if ($autorefresh > 0) { //renew cookie
  setcookie("autorefresh", "1", time() + 3600 * 24);
}
$OBJECTS['autorefresh'] = 0;
if (isset($_GET['id']) || !isset($_GET['new'])) {
  $OBJECTS['autorefresh'] = $autorefresh;
  $OBJECTS['autorefreshUrl'] = "";
}

if (isset($_GET['id'])) {
  if ($LOGIN->getLevel() < DAccessLevel::READ_ONLY) {
    $TEMPLATE = new Template("restricted");
    die($TEMPLATE->render($OBJECTS));
  }
  
  $TEMPLATE = new Template("tasks/detail");
  $task = $FACTORIES::getTaskFactory()->get($_GET['id']);
  if ($task == null) {
    UI::printError("ERROR", "Invalid task ID!");
  }
  $OBJECTS['task'] = $task;
  
  if ($task->getHashlistId() != null) {
    $hashlist = $FACTORIES::getHashlistFactory()->get($task->getHashlistId());
    $OBJECTS['hashlist'] = $hashlist;
    $hashtype = $FACTORIES::getHashTypeFactory()->get($hashlist->getHashtypeId());
    $OBJECTS['hashtype'] = $hashtype;
  }
  
  $isActive = 0;
  $activeChunks = array();
  $activeChunksIds = new DataSet();
  $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
  $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
  $activeAgents = new DataSet();
  $agentsSpeed = new DataSet();
  $currentSpeed = 0;
  foreach ($chunks as $chunk) {
    if (time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) < $CONFIG->getVal(DConfig::CHUNK_TIMEOUT) && $chunk->getRprogress() < 10000) {
      $isActive = 1;
      $activeChunks[] = $chunk;
      $activeChunksIds->addValue($chunk->getId(), true);
      $activeAgents->addValue($chunk->getAgentId(), true);
      $agentsSpeed->addValue($chunk->getAgentId(), $chunk->getSpeed());
      $currentSpeed += $chunk->getSpeed();
    }
    else {
      $activeChunksIds->addValue($chunk->getId(), false);
    }
  }
  $OBJECTS['isActive'] = $isActive;
  $OBJECTS['currentSpeed'] = $currentSpeed;
  
  $agentsBench = new DataSet();
  $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
  $assignments = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF));
  foreach ($assignments as $assignment) {
    $agentsBench->addValue($assignment->getAgentId(), $assignment->getBenchmark());
  }
  
  $cProgress = 0;
  $chunkIntervals = array();
  $agentsProgress = new DataSet();
  $agentsSpent = new DataSet();
  $agentsCracked = new DataSet();
  $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
  $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
  foreach ($chunks as $chunk) {
    if ($chunk->getDispatchTime() > 0 && $chunk->getSolveTime() > 0) {
      $chunkIntervals[] = array("start" => $chunk->getDispatchTime(), "stop" => $chunk->getSolveTime());
    }
    $cProgress += $chunk->getProgress();
    if (!$agentsProgress->getVal($chunk->getAgentId())) {
      $agentsProgress->addValue($chunk->getAgentId(), $chunk->getProgress());
      $agentsCracked->addValue($chunk->getAgentId(), $chunk->getCracked());
      $agentsSpent->addValue($chunk->getAgentId(), max($chunk->getSolveTime() - $chunk->getDispatchTime(), 0));
    }
    else {
      $agentsProgress->addValue($chunk->getAgentId(), $agentsProgress->getVal($chunk->getAgentId()) + $chunk->getProgress());
      $agentsCracked->addValue($chunk->getAgentId(), $agentsCracked->getVal($chunk->getAgentId()) + $chunk->getCracked());
      $agentsSpent->addValue($chunk->getAgentId(), $agentsSpent->getVal($chunk->getAgentId()) + max($chunk->getSolveTime() - $chunk->getDispatchTime(), 0));
    }
  }
  $OBJECTS['agentsProgress'] = $agentsProgress;
  $OBJECTS['agentsSpent'] = $agentsSpent;
  $OBJECTS['agentsCracked'] = $agentsCracked;
  $OBJECTS['cProgress'] = $cProgress;
  
  $timeSpent = 0;
  for ($i = 1; $i <= sizeof($chunkIntervals); $i++) {
    if (isset($chunkIntervals[$i]) && $chunkIntervals[$i]["start"] <= $chunkIntervals[$i - 1]["stop"]) {
      $chunkIntervals[$i]["start"] = $chunkIntervals[$i - 1]["start"];
      if ($chunkIntervals[$i]["stop"] < $chunkIntervals[$i - 1]["stop"]) {
        $chunkIntervals[$i]["stop"] = $chunkIntervals[$i - 1]["stop"];
      }
    }
    else {
      $timeSpent += ($chunkIntervals[$i - 1]["stop"] - $chunkIntervals[$i - 1]["start"]);
    }
  }
  $OBJECTS['timeSpent'] = $timeSpent;
  if ($task->getKeyspace() != 0 && ($cProgress / $task->getKeyspace()) != 0) {
    $OBJECTS['timeLeft'] = round($timeSpent / ($cProgress / $task->getKeyspace()) - $timeSpent);
  }
  else {
    $OBJECTS['timeLeft'] = -1;
  }
  
  $qF = new QueryFilter(TaskFile::TASK_ID, $task->getId(), "=", $FACTORIES::getTaskFileFactory());
  $jF = new JoinFilter($FACTORIES::getTaskFileFactory(), TaskFile::FILE_ID, File::FILE_ID);
  $joinedFiles = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
  $OBJECTS['attachedFiles'] = $joinedFiles[$FACTORIES::getFileFactory()->getModelName()];
  
  $jF = new JoinFilter($FACTORIES::getAssignmentFactory(), Assignment::AGENT_ID, Agent::AGENT_ID);
  $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=", $FACTORIES::getAssignmentFactory());
  $joinedAgents = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
  $assignedAgents = array();
  foreach ($joinedAgents[$FACTORIES::getAgentFactory()->getModelName()] as $agent) {
    $agent = \DBA\Util::cast($agent, \DBA\Agent::class);
    $assignedAgents[] = $agent->getId();
  }
  $OBJECTS['agents'] = $joinedAgents[$FACTORIES::getAgentFactory()->getModelName()];
  $OBJECTS['activeAgents'] = $activeAgents;
  $OBJECTS['agentsBench'] = $agentsBench;
  $OBJECTS['agentsSpeed'] = $agentsSpeed;
  
  $assignAgents = array();
  $allAgents = $FACTORIES::getAgentFactory()->filter(array());
  foreach ($allAgents as $agent) {
    if (!in_array($agent->getId(), $assignedAgents)) {
      $assignAgents[] = $agent;
    }
  }
  $OBJECTS['assignAgents'] = $assignAgents;
  
  if (isset($_GET['allagents'])) {
    $OBJECTS['showAllAgents'] = true;
    $allAgentsSpent = new DataSet();
    $allAgents = new DataSet();
    $agentObjects = array();
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=", $FACTORIES::getChunkFactory());
    $jF = new JoinFilter($FACTORIES::getChunkFactory(), Chunk::AGENT_ID, Agent::AGENT_ID);
    $joinedAgents = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    for ($i = 0; $i < sizeof($joinedAgents[$FACTORIES::getAgentFactory()->getModelName()]); $i++) {
      $chunk = \DBA\Util::cast($joinedAgents[$FACTORIES::getChunkFactory()->getModelName()][$i], \DBA\Chunk::class);
      $agent = \DBA\Util::cast($joinedAgents[$FACTORIES::getAgentFactory()->getModelName()][$i], \DBA\Agent::class);
      if ($allAgents->getVal($agent->getId()) == null) {
        $allAgents->addValue($agent->getId(), $agent);
        $agentObjects[] = $agent;
      }
      if ($chunk->getSolveTime() > $chunk->getDispatchTime()) {
        if ($allAgentsSpent->getVal($agent->getId()) == null) {
          $allAgentsSpent->addValue($agent->getId(), $chunk->getSolveTime() - $chunk->getDispatchTime());
        }
        else {
          $allAgentsSpent->addValue($agent->getId(), $allAgentsSpent->getVal($agent->getId()) + $chunk->getSolveTime() - $chunk->getDispatchTime());
        }
      }
    }
    $OBJECTS['agentObjects'] = $agentObjects;
    $OBJECTS['allAgentsSpent'] = $allAgentsSpent;
  }
  
  if (isset($_GET['all'])) {
    $OBJECTS['chunkFilter'] = 1;
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $oF = new OrderFilter(Chunk::SOLVE_TIME, "DESC LIMIT 100");
    $OBJECTS['chunks'] = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));
    $OBJECTS['activeChunks'] = $activeChunksIds;
  }
  else {
    $OBJECTS['chunkFilter'] = 0;
    $OBJECTS['chunks'] = $activeChunks;
    $OBJECTS['activeChunks'] = $activeChunksIds;
  }
  
  $agents = $FACTORIES::getAgentFactory()->filter(array());
  $fullAgents = new DataSet();
  foreach ($agents as $agent) {
    $fullAgents->addValue($agent->getId(), $agent);
  }
  $OBJECTS['fullAgents'] = $fullAgents;
}
else if (isset($_GET['new'])) {
  if ($LOGIN->getLevel() < DAccessLevel::READ_ONLY) {
    $TEMPLATE = new Template("restricted");
    die($TEMPLATE->render($OBJECTS));
  }
  $TEMPLATE = new Template("tasks/new");
  $MENU->setActive("tasks_new");
  $orig = 0;
  $copy = new Task(0, "", "", null, $CONFIG->getVal(DConfig::CHUNK_DURATION), $CONFIG->getVal(DConfig::STATUS_TIMER), 0, 0, 0, 0, "", 0, 1, 0);
  if (isset($_GET["copy"])) {
    //copied from a task
    $copy = $FACTORIES::getTaskFactory()->get($_GET['copy']);
    if ($copy != null) {
      $orig = $copy->getId();
      $copy->setId(0);
      $match = array();
      if (preg_match('/\(copy([0-9]+)\)/i', $copy->getTaskName(), $match)) {
        $name = $copy->getTaskName();
        $name = str_replace($match[0], "(copy" . (++$match[1]) . ")", $name);
        $copy->setTaskName($name);
      }
      else {
        $copy->setTaskName($copy->getTaskName() . " (copy1)");
      }
    }
  }
  
  if (strpos($copy->getAttackCmd(), $CONFIG->getVal(DConfig::HASHLIST_ALIAS)) === false) {
    $copy->setAttackCmd($CONFIG->getVal(DConfig::HASHLIST_ALIAS) . " " . $copy->getAttackCmd());
  }
  
  $OBJECTS['orig'] = $orig;
  $OBJECTS['copy'] = $copy;
  
  $lists = array();
  $set = new DataSet();
  $set->addValue('id', null);
  $set->addValue("name", "(pre-configured task)");
  $lists[] = $set;
  $res = $FACTORIES::getHashlistFactory()->filter(array());
  foreach ($res as $list) {
    $set = new DataSet();
    $set->addValue('id', $list->getId());
    $set->addValue('name', $list->getHashlistName());
    $lists[] = $set;
  }
  $OBJECTS['lists'] = $lists;
  
  $origFiles = array();
  if ($orig > 0) {
    $qF = new QueryFilter(TaskFile::TASK_ID, $orig, "=");
    $ff = $FACTORIES::getTaskFileFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($ff as $f) {
      $origFiles[] = $f->getFileId();
    }
  }
  $oF = new OrderFilter(File::FILENAME, "ASC");
  $allFiles = $FACTORIES::getFileFactory()->filter(array($FACTORIES::ORDER => $oF));
  $rules = array();
  $wordlists = array();
  foreach ($allFiles as $singleFile) {
    $set = new DataSet();
    $checked = "0";
    if (in_array($singleFile->getId(), $origFiles)) {
      $checked = "1";
    }
    $set->addValue('checked', $checked);
    $set->addValue('file', $singleFile);
    if ($singleFile->getFileType() == 1) {
      $rules[] = $set;
    }
    else {
      $wordlists[] = $set;
    }
  }
  $OBJECTS['wordlists'] = $wordlists;
  $OBJECTS['rules'] = $rules;
}
else {
  $jF = new JoinFilter($FACTORIES::getHashlistFactory(), Hashlist::HASHLIST_ID, Task::HASHLIST_ID);
  $oF1 = new OrderFilter(Task::PRIORITY, "DESC");
  $oF2 = new OrderFilter(Task::TASK_ID, "ASC");
  $joinedTasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::JOIN => $jF, $FACTORIES::ORDER => array($oF1, $oF2)));
  $tasks = array();
  for ($z = 0; $z < sizeof($joinedTasks[$FACTORIES::getTaskFactory()->getModelName()]); $z++) {
    $set = new DataSet();
    $set->addValue('Task', $joinedTasks[$FACTORIES::getTaskFactory()->getModelName()][$z]);
    $set->addValue('Hashlist', $joinedTasks[$FACTORIES::getHashlistFactory()->getModelName()][$z]);
    
    $task = \DBA\Util::cast($joinedTasks[$FACTORIES::getTaskFactory()->getModelName()][$z], Task::class);
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $progress = 0;
    $cracked = 0;
    $maxTime = 0;
    foreach ($chunks as $chunk) {
      $progress += $chunk->getProgress();
      $cracked += $chunk->getCracked();
      if ($chunk->getDispatchTime() > $maxTime) {
        $maxTime = $chunk->getDispatchTime();
      }
      if ($chunk->getSolveTime() > $maxTime) {
        $maxTime = $chunk->getSolveTime();
      }
    }
    
    $isActive = false;
    if (time() - $maxTime < $CONFIG->getVal(DConfig::CHUNK_TIMEOUT)) {
      $isActive = true;
    }
    
    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF));
    
    $qF = new QueryFilter(TaskFile::TASK_ID, $task->getId(), "=", $FACTORIES::getTaskFileFactory());
    $jF = new JoinFilter($FACTORIES::getTaskFileFactory(), TaskFile::FILE_ID, File::FILE_ID);
    $joinedFiles = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    $sizes = 0;
    $secret = false;
    for ($x = 0; $x < sizeof($joinedFiles[$FACTORIES::getFileFactory()->getModelName()]); $x++) {
      $file = \DBA\Util::cast($joinedFiles[$FACTORIES::getFileFactory()->getModelName()][$x], \DBA\File::class);
      $sizes += $file->getSize();
      if ($file->getSecret() == '1') {
        $secret = true;
      }
    }
    
    $set->addValue('numFiles', sizeof($joinedFiles[$FACTORIES::getFileFactory()->getModelName()]));
    $set->addValue('filesSize', $sizes);
    $set->addValue('fileSecret', $secret);
    $set->addValue('numAssignments', sizeof($assignments));
    $set->addValue('isActive', $isActive);
    $set->addValue('sumprog', $progress);
    $set->addValue('cracked', $cracked);
    $set->addValue('numChunks', sizeof($chunks));
    
    $tasks[] = $set;
  }
  $OBJECTS['tasks'] = $tasks;
}

echo $TEMPLATE->render($OBJECTS);




