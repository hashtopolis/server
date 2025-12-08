<?php

use DBA\AccessGroupAgent;
use DBA\Agent;
use DBA\Assignment;
use DBA\Chunk;
use DBA\CrackerBinary;
use DBA\File;
use DBA\FileTask;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\Preprocessor;
use DBA\QueryFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
AccessControl::getInstance()->checkPermission(array_merge(DViewControl::TASKS_VIEW_PERM, DAccessControl::RUN_TASK_ACCESS));

Template::loadInstance("tasks/index");
Menu::get()->setActive("tasks_list");

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
UI::add('autorefresh', 0);
if (isset($_GET['id']) || !isset($_GET['new'])) {
  UI::add('autorefresh', $autorefresh);
  UI::add('autorefreshUrl', "");
}

if (isset($_GET['id'])) {
  AccessControl::getInstance()->checkPermission(DViewControl::TASKS_VIEW_PERM);
  Template::loadInstance("tasks/detail");
  $task = Factory::getTaskFactory()->get($_GET['id']);
  if ($task == null) {
    UI::printError("ERROR", "Invalid task ID!");
  }
  UI::add('task', $task);
  $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
  UI::add('taskWrapper', $taskWrapper);
  
  $fileInfo = Util::getFileInfo($task, AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser()));
  if ($fileInfo[4]) {
    UI::printError("ERROR", "No access to this task!");
  }
  
  $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
  if (!AccessUtils::userCanAccessHashlists($hashlist, Login::getInstance()->getUser())) {
    UI::printError("ERROR", "No access to this task!");
  }

  UI::add('hashlist', $hashlist);
  $hashtype = Factory::getHashTypeFactory()->get($hashlist->getHashtypeId());
  UI::add('hashtype', $hashtype);
  
  $isActive = 0;
  $activeChunks = [];
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
  UI::add('isActive', $isActive);
  UI::add('currentSpeed', $currentSpeed);
  
  $agentsBench = new DataSet();
  $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
  $assignments = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF]);
  foreach ($assignments as $assignment) {
    $agentsBench->addValue($assignment->getAgentId(), $assignment->getBenchmark());
  }
  
  $cProgress = 0;
  $chunkIntervals = [];
  $agentsProgress = new DataSet();
  $agentsSpent = new DataSet();
  $agentsCracked = new DataSet();
  $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
  $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
  foreach ($chunks as $chunk) {
    if ($chunk->getDispatchTime() > 0 && $chunk->getSolveTime() > 0) {
      $chunkIntervals[] = ["start" => $chunk->getDispatchTime(), "stop" => $chunk->getSolveTime()];
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
  UI::add('agentsProgress', $agentsProgress);
  UI::add('agentsSpent', $agentsSpent);
  UI::add('agentsCracked', $agentsCracked);
  UI::add('cProgress', $cProgress);
  
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
  UI::add('timeSpent', $timeSpent);
  
  if ($task->getKeyspace() != 0 && ($cProgress / $task->getKeyspace()) != 0) {
    UI::add('timeLeft', round($timeSpent / ($cProgress / $task->getKeyspace()) - $timeSpent));
  }
  else {
    UI::add('timeLeft', -1);
  }
  
  $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=", Factory::getFileTaskFactory());
  $jF = new JoinFilter(Factory::getFileTaskFactory(), FileTask::FILE_ID, File::FILE_ID);
  $joinedFiles = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
  UI::add('attachedFiles', $joinedFiles[Factory::getFileFactory()->getModelName()]);
  
  $jF = new JoinFilter(Factory::getAssignmentFactory(), Assignment::AGENT_ID, Agent::AGENT_ID);
  $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=", Factory::getAssignmentFactory());
  $joinedAgents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
  $assignedAgents = array();
  foreach ($joinedAgents[Factory::getAgentFactory()->getModelName()] as $agent) {
    /** @var $agent Agent */
    $assignedAgents[] = $agent->getId();
  }
  UI::add('agents', $joinedAgents[Factory::getAgentFactory()->getModelName()]);
  UI::add('activeAgents', $activeAgents);
  UI::add('agentsBench', $agentsBench);
  UI::add('agentsSpeed', $agentsSpeed);
  
  $assignAgents = array();
  $qF = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $hashlist->getAccessGroupId(), "=", Factory::getAccessGroupAgentFactory());
  $jF = new JoinFilter(Factory::getAccessGroupAgentFactory(), AccessGroupAgent::AGENT_ID, Agent::AGENT_ID);
  $allAgents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF])[Factory::getAgentFactory()->getModelName()];
  foreach ($allAgents as $agent) {
    if (!in_array($agent->getId(), $assignedAgents)) {
      $assignAgents[] = $agent;
    }
  }
  UI::add('assignAgents', $assignAgents);
  
  $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
  UI::add('numChunks', Factory::getChunkFactory()->countFilter([Factory::FILTER => $qF]));
  
  UI::add('showAllAgents', false);
  if (isset($_GET['allagents'])) {
    UI::add('showAllAgents', true);
    $allAgentsSpent = new DataSet();
    $allAgents = new DataSet();
    $agentObjects = array();
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=", Factory::getChunkFactory());
    $jF = new JoinFilter(Factory::getChunkFactory(), Chunk::AGENT_ID, Agent::AGENT_ID);
    $joinedAgents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $agents Agent[] */
    $agents = $joinedAgents[Factory::getAgentFactory()->getModelName()];
    for ($i = 0; $i < sizeof($agents); $i++) {
      /** @var $chunk Chunk */
      $chunk = $joinedAgents[Factory::getChunkFactory()->getModelName()][$i];
      $agent = $agents[$i];
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
    UI::add('agentObjects', $agentObjects);
    UI::add('allAgentsSpent', $allAgentsSpent);
  }
  
  UI::add('activeChunks', $activeChunksIds);
  
  if (isset($_GET['all'])) {
    if ($_GET['all'] == 1) {
      //  show last 100
      UI::add('chunkFilter', 1);
      $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
      $oF = new OrderFilter(Chunk::SOLVE_TIME, "DESC LIMIT 100");
      UI::add('chunks', Factory::getChunkFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]));
    }
    else if ($_GET['all'] == 2) {
      UI::add('chunkFilter', 2);
      //  show all, page by page
      
      if (!isset($_GET['pagesize'])) {
        $chunkPageSize = 100;
      }
      else {
        $chunkPageSize = intval($_GET['pagesize']);
      }
      if (!isset($_GET['page'])) {
        $page = 0;
      }
      else {
        $page = intval($_GET['page']);
      }
      UI::add('page', $page);
      $limit = $page * $chunkPageSize;
      $oFp = new OrderFilter(Chunk::SOLVE_TIME, "DESC LIMIT $chunkPageSize OFFSET $limit", Factory::getChunkFactory());
      UI::add('chunksPageTitle', "All chunks (page " . ($page + 1) . ")");
      
      $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
      $oF = new OrderFilter(Chunk::SOLVE_TIME, "DESC");
      
      $numEntries = Factory::getChunkFactory()->countFilter([Factory::FILTER => $qF]);
      UI::add('maxpage', floor($numEntries / $chunkPageSize));
      
      UI::add('chunks', Factory::getChunkFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => [$oF, $oFp]]));
      UI::add('activeChunks', $activeChunksIds);
    }
    else {
      //  show active only
      UI::add('chunkFilter', 0);
      UI::add('chunks', $activeChunks);
    }
  }
  else {
    //  show active only by default
    UI::add('chunkFilter', 0);
    UI::add('chunks', $activeChunks);
  }
  
  if ($task->getUsePreprocessor()) {
    try {
      UI::add('preprocessor', PreprocessorUtils::getPreprocessor($task->getUsePreprocessor()));
    }
    catch (HTException $e) {
      UI::printError("ERROR", "Failed to load preprocessor!");
    }
  }
  
  $agents = Factory::getAgentFactory()->filter([]);
  $fullAgents = new DataSet();
  foreach ($agents as $agent) {
    $fullAgents->addValue($agent->getId(), $agent);
  }
  UI::add('fullAgents', $fullAgents);
  UI::add('pageTitle', "Task details for " . $task->getTaskName());
  
  // load graph data
  $data = Util::getSpeedDataSet($task->getId(), 50, 0, $task->getStatusTimer());
  if (sizeof($data) > 0) {
    $xlabels = [];
    $rawData = [];
    foreach ($data as $key => $val) {
      $xlabels[] = date(SConfig::getInstance()->getVal(DConfig::TIME_FORMAT), $key);
      $rawData[] = $val;
    }
    $datasets[0] = [
      "label" => "H/s",
      "fill" => false,
      "lineTension" => 0.2,
      "borderColor" => "#008000",
      "backgroundColor" => "#008000",
      "data" => $rawData
    ];
    UI::add("taskSpeedXLabels", json_encode($xlabels));
    UI::add("taskSpeed", json_encode($datasets));
  }
  UI::add('taskGraph', (sizeof($data) > 0) ? 1 : 0);
}
else if (isset($_GET['new'])) {
  AccessControl::getInstance()->checkPermission(array_merge(DAccessControl::RUN_TASK_ACCESS, DAccessControl::CREATE_TASK_ACCESS));
  Template::loadInstance("tasks/new");
  Menu::get()->setActive("tasks_new");
  $orig = 0;
  $origTask = null;
  $origType = 0;
  $hashlistId = 0;
  $copy = null;
  if (isset($_GET["copy"])) {
    AccessControl::getInstance()->checkPermission(DAccessControl::CREATE_TASK_ACCESS); // enforce additional permission for this
    
    //copied from a task
    $copy = Factory::getTaskFactory()->get($_GET['copy']);
    if ($copy != null) {
      $orig = $copy->getId();
      $origTask = clone $copy;
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
  
  UI::add('accessGroups', AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser()));
  $accessGroupIds = Util::arrayOfIds(UI::get('accessGroups'));
  
  UI::add('orig', $orig);
  UI::add('copy', $copy);
  UI::add('origType', $origType);
  UI::add('hashlistId', $hashlistId);
  
  $lists = array();
  $res = HashlistUtils::getHashlists(Login::getInstance()->getUser());
  foreach ($res as $list) {
    $set = new DataSet();
    $set->addValue('id', $list->getId());
    $set->addValue('name', $list->getHashlistName());
    $lists[] = $set;
  }
  $res = HashlistUtils::getSuperhashlists(Login::getInstance()->getUser());
  foreach ($res as $list) {
    $set = new DataSet();
    $set->addValue('id', $list->getId());
    $set->addValue('name', $list->getHashlistName());
    $lists[] = $set;
  }
  UI::add('lists', $lists);
  
  $oF = new OrderFilter(Preprocessor::NAME, "ASC");
  $preprocessors = Factory::getPreprocessorFactory()->filter([Factory::ORDER => $oF]);
  UI::add('preprocessors', $preprocessors);
  
  $origFiles = array();
  if ($origType == 1) {
    $origFiles = Util::arrayOfIds(TaskUtils::getFilesOfTask($origTask));
  }
  else if ($origType == 2) {
    $origFiles = Util::arrayOfIds(TaskUtils::getFilesOfPretask($origTask));
  }
  
  $arr = FileUtils::loadFilesByCategory(Login::getInstance()->getUser(), $origFiles);
  UI::add('wordlists', $arr[1]);
  UI::add('rules', $arr[0]);
  UI::add('other', $arr[2]);
  
  $oF = new OrderFilter(CrackerBinary::CRACKER_BINARY_ID, "DESC");
  UI::add('binaries', Factory::getCrackerBinaryTypeFactory()->filter([]));
  $versions = Factory::getCrackerBinaryFactory()->filter([Factory::ORDER => $oF]);
  usort($versions, ["Util", "versionComparisonBinary"]);
  $versions = array_reverse($versions);
  UI::add('versions', $versions);
  UI::add('pageTitle', "Create Task");
}
else {
  AccessControl::getInstance()->checkPermission(DViewControl::TASKS_VIEW_PERM);
  UI::add('showArchived', false);
  UI::add('pageTitle', "Tasks");
  if (isset($_GET['archived']) && $_GET['archived'] == 'true') {
    Util::loadTasks(true);
    UI::add('showArchived', true);
    UI::add('pageTitle', "Archived Tasks");
  }
  else {
    Util::loadTasks(false);
  }
}

echo Template::getInstance()->render(UI::getObjects());




