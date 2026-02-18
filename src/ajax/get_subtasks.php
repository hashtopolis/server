<?php

use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\Factory;

require_once(dirname(__FILE__) . "/../inc/startup/load.php");

// test if task exists
$taskWrapper = Factory::getTaskWrapperFactory()->get($_GET['taskWrapperId']);
if ($taskWrapper == null) {
  die("Invalid task wrapper!");
}
else if (!AccessUtils::userCanAccessTask($taskWrapper, Login::getInstance()->getUser())) {
  die("No access to task!");
}
$accessGroups = AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser());
$showArchived = ($_GET['showArchived']) ? true : false;

$qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
$oF = new OrderFilter(Task::PRIORITY, "DESC");
$tasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
$subtaskList = array();
$tasksDone = 0;
$isActive = false;
$cracked = 0;
$numAssignments = 0;
$numFiles = 0;
$fileSecret = false;
$filesSize = 0;
foreach ($tasks as $task) {
  $subSet = new DataSet();
  $subSet->addValue('color', $task->getColor());
  $subSet->addValue('taskId', $task->getId());
  $subSet->addValue('attackCmd', $task->getAttackCmd());
  $subSet->addValue('taskName', $task->getTaskName());
  $subSet->addValue('keyspace', $task->getKeyspace());
  $subSet->addValue('cpuOnly', $task->getIsCpuTask());
  $subSet->addValue('isSmall', $task->getIsSmall());
  $subSet->addValue('usePreprocessor', $task->getUsePreprocessor());
  $subSet->addValue('chunkTime', $task->getChunkTime());
  $subSet->addValue('taskProgress', $task->getKeyspaceProgress());
  $subSet->addValue('priority', $task->getPriority());
  $subSet->addValue('maxAgents', $task->getMaxAgents());
  $taskInfo = Util::getTaskInfo($task);
  $fileInfo = Util::getFileInfo($task, $accessGroups);
  $chunkInfo = Util::getChunkInfo($task);
  if ($fileInfo[4]) {
    continue;
  }
  $subSet->addValue('sumProgress', $taskInfo[0]);
  $subSet->addValue('numFiles', $fileInfo[0]);
  $subSet->addValue('fileSecret', $fileInfo[1]);
  $subSet->addValue('filesSize', $fileInfo[2]);
  $subSet->addValue('numChunks', $chunkInfo[0]);
  $subSet->addValue('isActive', $taskInfo[2]);
  $subSet->addValue('cracked', $taskInfo[1]);
  $subSet->addValue('numAssignments', $chunkInfo[2]);
  $subSet->addValue('performance', $taskInfo[4]);
  $subSet->addValue('speed', $taskInfo[5]);
  $subtaskList[] = $subSet;
}

Template::loadInstance("tasks/subtasks");
UI::add('subtaskList', $subtaskList);
UI::add('showArchived', $showArchived);
echo Template::getInstance()->render(UI::getObjects());
