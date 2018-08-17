<?php

use DBA\Agent;
use DBA\Assignment;
use DBA\Chunk;
use DBA\CrackerBinary;
use DBA\File;
use DBA\FilePretask;
use DBA\FileTask;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */
/** @var DataSet $CONFIG */

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
  $task = $FACTORIES::getTaskFactory()->get($_GET['id']);
  if ($task == null) {
    UI::printError("ERROR", "Invalid task ID!");
  }
  $OBJECTS['task'] = $task;
  $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
  $OBJECTS['taskWrapper'] = $taskWrapper;

  $fileInfo = Util::getFileInfo($task, AccessUtils::getAccessGroupsOfUser($LOGIN->getUser()));
  if($fileInfo[4]){
    UI::printError("ERROR", "No access to this task!");
  }

  $hashlist = $FACTORIES::getHashlistFactory()->get($taskWrapper->getHashlistId());
  $OBJECTS['hashlist'] = $hashlist;
  $hashtype = $FACTORIES::getHashTypeFactory()->get($hashlist->getHashtypeId());
  $OBJECTS['hashtype'] = $hashtype;

  $isActive = 0;
  $activeChunks = array();
  $activeChunksIds = new DataSet();
  $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
  $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
  $activeAgents = new DataSet();
  $agentsSpeed = new DataSet();
  $currentSpeed = 0;
  foreach ($chunks as $chunk) {
    if (time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) < $CONFIG->getVal(DConfig::CHUNK_TIMEOUT) && $chunk->getProgress() < 10000) {
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

  $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=", $FACTORIES::getFileTaskFactory());
  $jF = new JoinFilter($FACTORIES::getFileTaskFactory(), FileTask::FILE_ID, File::FILE_ID);
  $joinedFiles = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
  $OBJECTS['attachedFiles'] = $joinedFiles[$FACTORIES::getFileFactory()->getModelName()];

  $jF = new JoinFilter($FACTORIES::getAssignmentFactory(), Assignment::AGENT_ID, Agent::AGENT_ID);
  $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=", $FACTORIES::getAssignmentFactory());
  $joinedAgents = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
  $assignedAgents = array();
  foreach ($joinedAgents[$FACTORIES::getAgentFactory()->getModelName()] as $agent) {
    /** @var $agent Agent */
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

  $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
  $OBJECTS['numChunks'] = $FACTORIES::getChunkFactory()->countFilter(array($FACTORIES::FILTER => $qF));

  $OBJECTS['showAllAgents'] = false;
  if (isset($_GET['allagents'])) {
    $OBJECTS['showAllAgents'] = true;
    $allAgentsSpent = new DataSet();
    $allAgents = new DataSet();
    $agentObjects = array();
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=", $FACTORIES::getChunkFactory());
    $jF = new JoinFilter($FACTORIES::getChunkFactory(), Chunk::AGENT_ID, Agent::AGENT_ID);
    $joinedAgents = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    for ($i = 0; $i < sizeof($joinedAgents[$FACTORIES::getAgentFactory()->getModelName()]); $i++) {
      /** @var $chunk Chunk */
      $chunk = $joinedAgents[$FACTORIES::getChunkFactory()->getModelName()][$i];
      /** @var $agent Agent */
      $agent = $joinedAgents[$FACTORIES::getAgentFactory()->getModelName()][$i];
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
  $OBJECTS['pageTitle'] = "Task details for " . $task->getTaskName();
}
else if (isset($_GET['new'])) {
  $ACCESS_CONTROL->checkPermission(array_merge(DAccessControl::RUN_TASK_ACCESS, DAccessControl::CREATE_TASK_ACCESS));
  $TEMPLATE = new Template("tasks/new");
  $MENU->setActive("tasks_new");
  $orig = 0;
  $origType = 0;
  $hashlistId = 0;
  $copy = null;
  if (isset($_GET["copy"])) {
    $ACCESS_CONTROL->checkPermission(DAccessControl::CREATE_TASK_ACCESS); // enforce additional permission for this

    //copied from a task
    $copy = $FACTORIES::getTaskFactory()->get($_GET['copy']);
    if ($copy != null) {
      $orig = $copy->getId();
      $origType = 1;
      $hashlistId = $FACTORIES::getTaskWrapperFactory()->get($copy->getTaskWrapperId())->getHashlistId();
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
    $copy = $FACTORIES::getPretaskFactory()->get($_GET['copyPre']);
    if ($copy != null) {
      $orig = $copy->getId();
      $origType = 2;
      $copy = new Task(
        0,
        $copy->getTaskName(),
        $copy->getAttackCmd(),
        $copy->getChunkTime(),
        $copy->getStatusTimer(),
        0,
        0,
        $copy->getPriority(),
        $copy->getColor(),
        $copy->getIsSmall(),
        $copy->getIsCpuTask(),
        $copy->getUseNewBench(),
        0,
        0,
        $copy->getCrackerBinaryTypeId(),
        0,
        0,
        0,
        '',
        0,
        0
      );
    }
  }
  if ($copy === null) {
    $copy = new Task(
      0,
      "",
      "",
      $CONFIG->getVal(DConfig::CHUNK_DURATION),
      $CONFIG->getVal(DConfig::STATUS_TIMER),
      0,
      0,
      0,
      "",
      0,
      0,
      $CONFIG->getVal(DConfig::DEFAULT_BENCH),
      0,
      0,
      0,
      0,
      0,
      0,
      '',
      0,
      0
    );
  }
  if (strpos($copy->getAttackCmd(), $CONFIG->getVal(DConfig::HASHLIST_ALIAS)) === false) {
    $copy->setAttackCmd($CONFIG->getVal(DConfig::HASHLIST_ALIAS) . " " . $copy->getAttackCmd());
  }

  $OBJECTS['accessGroups'] = AccessUtils::getAccessGroupsOfUser($LOGIN->getUser());
  $accessGroupIds = Util::arrayOfIds($OBJECTS['accessGroups']);

  $OBJECTS['orig'] = $orig;
  $OBJECTS['copy'] = $copy;
  $OBJECTS['origType'] = $origType;
  $OBJECTS['hashlistId'] = $hashlistId;

  $lists = array();
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
    if ($origType == 1) {
      $qF = new QueryFilter(FileTask::TASK_ID, $orig, "=");
      $ff = $FACTORIES::getFileTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
      foreach ($ff as $f) {
        $origFiles[] = $f->getFileId();
      }
    }
    else {
      $qF = new QueryFilter(FilePretask::PRETASK_ID, $orig, "=");
      $ff = $FACTORIES::getFilePretaskFactory()->filter(array($FACTORIES::FILTER => $qF));
      foreach ($ff as $f) {
        $origFiles[] = $f->getFileId();
      }
    }
  }

  $arr = FileUtils::loadFilesByCategory($LOGIN->getUser(), $origFiles);
  $OBJECTS['wordlists'] = $arr[0];
  $OBJECTS['rules'] = $arr[1];
  $OBJECTS['other'] = $arr[2];

  $oF = new OrderFilter(CrackerBinary::CRACKER_BINARY_ID, "DESC");
  $OBJECTS['binaries'] = $FACTORIES::getCrackerBinaryTypeFactory()->filter(array());
  $versions = $FACTORIES::getCrackerBinaryFactory()->filter(array($FACTORIES::ORDER => $oF));
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




