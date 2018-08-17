<?php

use DBA\Agent;
use DBA\Assignment;
use DBA\Chunk;
use DBA\CrackerBinary;
use DBA\File;
use DBA\FileTask;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
$ACCESS_CONTROL->checkPermission(array_merge(DViewControl::TASKS_VIEW_PERM, DAccessControl::RUN_TASK_ACCESS));

$TEMPLATE = new Template("tasks/index");
$MENU->setActive("tasks_list");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
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
  $ACCESS_CONTROL->checkPermission(DViewControl::TASKS_VIEW_PERM);
  $TEMPLATE = new Template("tasks/detail");
  $task = Factory::getTaskFactory()->get($_GET['id']);
  if ($task == null) {
    UI::printError("ERROR", "Invalid task ID!");
  }
  $OBJECTS['task'] = $task;
  $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
  $OBJECTS['taskWrapper'] = $taskWrapper;

  $fileInfo = Util::getFileInfo($task, AccessUtils::getAccessGroupsOfUser($LOGIN->getUser()));
  if($fileInfo[4]){
    UI::printError("ERROR", "No access to this task!");
  }

  $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
  $OBJECTS['hashlist'] = $hashlist;
  $hashtype = Factory::getHashTypeFactory()->get($hashlist->getHashtypeId());
  $OBJECTS['hashtype'] = $hashtype;

  $isActive = 0;
  $activeChunks = array();
  $activeChunksIds = new DataSet();
  $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
  $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
  $activeAgents = new DataSet();
  $agentsSpeed = new DataSet();
  $currentSpeed = 0;
  foreach ($chunks as $chunk) {
    if (time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) < SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT) && $chunk->getProgress() < 10000) {
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
  $assignments = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF]);
  foreach ($assignments as $assignment) {
    $agentsBench->addValue($assignment->getAgentId(), $assignment->getBenchmark());
  }

  $cProgress = 0;
  $chunkIntervals = array();
  $agentsProgress = new DataSet();
  $agentsSpent = new DataSet();
  $agentsCracked = new DataSet();
  $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
  $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
  foreach ($chunks as $chunk) {
    if ($chunk->getDispatchTime() > 0 && $chunk->getSolveTime() > 0) {
      $chunkIntervals[] = array("start" => $chunk->getDispatchTime(), "stop" => $chunk->getSolveTime());
    }
    $cProgress += $chunk->getCheckpoint() - $chunk->getSkip();
    if (!$agentsProgress->getVal($chunk->getAgentId())) {
      $agentsProgress->addValue($chunk->getAgentId(), $chunk->getCheckpoint() - $chunk->getSkip());
      $agentsCracked->addValue($chunk->getAgentId(), $chunk->getCracked());
      $agentsSpent->addValue($chunk->getAgentId(), max($chunk->getSolveTime() - $chunk->getDispatchTime(), 0));
    }
    else {
      $agentsProgress->addValue($chunk->getAgentId(), $agentsProgress->getVal($chunk->getAgentId()) + $chunk->getCheckpoint() - $chunk->getSkip());
      $agentsCracked->addValue($chunk->getAgentId(), $agentsCracked->getVal($chunk->getAgentId()) + $chunk->getCracked());
      $agentsSpent->addValue($chunk->getAgentId(), $agentsSpent->getVal($chunk->getAgentId()) + max($chunk->getSolveTime() - $chunk->getDispatchTime(), 0));
    }
  }
  $OBJECTS['agentsProgress'] = $agentsProgress;
  $OBJECTS['agentsSpent'] = $agentsSpent;
  $OBJECTS['agentsCracked'] = $agentsCracked;
  $OBJECTS['cProgress'] = $cProgress;

  $timeChunks = $chunks;
  usort($timeChunks, "Util::compareChunksTime");
  $timeSpent = 0;
  $current = 0;
  foreach ($timeChunks as $c) {
    if ($c->getDispatchTime() > $current) {
      $timeSpent += $c->getSolveTime() - $c->getDispatchTime();
      $current = $c->getSolveTime();
    }
    else if ($c->getSolveTime() > $current) {
      $timeSpent += $c->getSolveTime() - $current;
      $current = $c->getSolveTime();
    }
  }
  $OBJECTS['timeSpent'] = $timeSpent;

  if ($task->getKeyspace() != 0 && ($cProgress / $task->getKeyspace()) != 0) {
    $OBJECTS['timeLeft'] = round($timeSpent / ($cProgress / $task->getKeyspace()) - $timeSpent);
  }
  else {
    $OBJECTS['timeLeft'] = -1;
  }

  $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=", Factory::getFileTaskFactory());
  $jF = new JoinFilter(Factory::getFileTaskFactory(), FileTask::FILE_ID, File::FILE_ID);
  $joinedFiles = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
  $OBJECTS['attachedFiles'] = $joinedFiles[Factory::getFileFactory()->getModelName()];

  $jF = new JoinFilter(Factory::getAssignmentFactory(), Assignment::AGENT_ID, Agent::AGENT_ID);
  $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=", Factory::getAssignmentFactory());
  $joinedAgents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
  $assignedAgents = array();
  foreach ($joinedAgents[Factory::getAgentFactory()->getModelName()] as $agent) {
    /** @var $agent Agent */
    $assignedAgents[] = $agent->getId();
  }
  $OBJECTS['agents'] = $joinedAgents[Factory::getAgentFactory()->getModelName()];
  $OBJECTS['activeAgents'] = $activeAgents;
  $OBJECTS['agentsBench'] = $agentsBench;
  $OBJECTS['agentsSpeed'] = $agentsSpeed;

  $assignAgents = array();
  $allAgents = Factory::getAgentFactory()->filter([]);
  foreach ($allAgents as $agent) {
    if (!in_array($agent->getId(), $assignedAgents)) {
      $assignAgents[] = $agent;
    }
  }
  $OBJECTS['assignAgents'] = $assignAgents;

  $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
  $OBJECTS['numChunks'] = Factory::getChunkFactory()->countFilter([Factory::FILTER => $qF]);

  $OBJECTS['showAllAgents'] = false;
  if (isset($_GET['allagents'])) {
    $OBJECTS['showAllAgents'] = true;
    $allAgentsSpent = new DataSet();
    $allAgents = new DataSet();
    $agentObjects = array();
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=", Factory::getChunkFactory());
    $jF = new JoinFilter(Factory::getChunkFactory(), Chunk::AGENT_ID, Agent::AGENT_ID);
    $joinedAgents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    for ($i = 0; $i < sizeof($joinedAgents[Factory::getAgentFactory()->getModelName()]); $i++) {
      /** @var $chunk Chunk */
      $chunk = $joinedAgents[Factory::getChunkFactory()->getModelName()][$i];
      /** @var $agent Agent */
      $agent = $joinedAgents[Factory::getAgentFactory()->getModelName()][$i];
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
    $OBJECTS['chunks'] = Factory::getChunkFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
    $OBJECTS['activeChunks'] = $activeChunksIds;
  }
  else {
    $OBJECTS['chunkFilter'] = 0;
    $OBJECTS['chunks'] = $activeChunks;
    $OBJECTS['activeChunks'] = $activeChunksIds;
  }

  $agents = Factory::getAgentFactory()->filter([]);
  $fullAgents = new DataSet();
  foreach ($agents as $agent) {
    $fullAgents->addValue($agent->getId(), $agent);
  }
  $OBJECTS['fullAgents'] = $fullAgents;
  $OBJECTS['pageTitle'] = "Task details for " . $task->getTaskName();
}
else if (isset($_GET['new'])) {
  $ACCESS_CONTROL->checkPermission(array_merge(DAccessControl::RUN_TASK_ACCESS, DAccessControl::CREATE_TASK_ACCESS));
  $TEMPLATE = new Template("tasks/new");
  $MENU->setActive("tasks_new");
  $orig = 0;
  $origTask = null;
  $origType = 0;
  $hashlistId = 0;
  $copy = null;
  if (isset($_GET["copy"])) {
    $ACCESS_CONTROL->checkPermission(DAccessControl::CREATE_TASK_ACCESS); // enforce additional permission for this

    //copied from a task
    $copy = Factory::getTaskFactory()->get($_GET['copy']);
    if ($copy != null) {
      $orig = $copy->getId();
      $origTask = $copy;
      $origType = 1;
      $hashlistId = Factory::getTaskWrapperFactory()->get($copy->getTaskWrapperId())->getHashlistId();
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
  else if (isset($_GET["copyPre"])) {
    //copied from a task
    $copy = Factory::getPretaskFactory()->get($_GET['copyPre']);
    if ($copy != null) {
      $orig = $copy->getId();
      $origTask = $copy;
      $origType = 2;
      $copy = TaskUtils::getFromPretask($copy);
    }
  }
  if ($copy === null) {
    $copy = TaskUtils::getDefault();
  }
  if (strpos($copy->getAttackCmd(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS)) === false) {
    $copy->setAttackCmd(SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS) . " " . $copy->getAttackCmd());
  }

  $OBJECTS['accessGroups'] = AccessUtils::getAccessGroupsOfUser($LOGIN->getUser());
  $accessGroupIds = Util::arrayOfIds($OBJECTS['accessGroups']);

  $OBJECTS['orig'] = $orig;
  $OBJECTS['copy'] = $copy;
  $OBJECTS['origType'] = $origType;
  $OBJECTS['hashlistId'] = $hashlistId;

  $lists = array();
  $res = Factory::getHashlistFactory()->filter([]);
  foreach ($res as $list) {
    $set = new DataSet();
    $set->addValue('id', $list->getId());
    $set->addValue('name', $list->getHashlistName());
    $lists[] = $set;
  }
  $OBJECTS['lists'] = $lists;

  $origFiles = array();
  if ($origType == 1) {
    $origFiles = Util::arrayOfIds(TaskUtils::getFilesOfTask($origTask));
  }
  else if($origType == 2){
    $origFiles = Util::arrayOfIds(TaskUtils::getFilesOfPretask($origTask));
  }

  $arr = FileUtils::loadFilesByCategory($LOGIN->getUser(), $origFiles);
  $OBJECTS['wordlists'] = $arr[1];
  $OBJECTS['rules'] = $arr[0];
  $OBJECTS['other'] = $arr[2];

  $oF = new OrderFilter(CrackerBinary::CRACKER_BINARY_ID, "DESC");
  $OBJECTS['binaries'] = Factory::getCrackerBinaryTypeFactory()->filter([]);
  $versions = Factory::getCrackerBinaryFactory()->filter([Factory::ORDER => $oF]);
  usort($versions, array("Util", "versionComparisonBinary"));
  $OBJECTS['versions'] = $versions;
  $OBJECTS['pageTitle'] = "Create Task";
}
else {
  $ACCESS_CONTROL->checkPermission(DViewControl::TASKS_VIEW_PERM);
  $OBJECTS['showArchived'] = false;
  $OBJECTS['pageTitle'] = "Tasks";
  if(isset($_GET['archived']) && $_GET['archived'] == 'true'){
    Util::loadTasks(true);
    $OBJECTS['showArchived'] = true;
    $OBJECTS['pageTitle'] = "Archived Tasks";
  }
  else{
    Util::loadTasks(false);
  }
}

echo $TEMPLATE->render($OBJECTS);




