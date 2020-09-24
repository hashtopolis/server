<?php

use DBA\AccessGroupUser;
use DBA\Chunk;
use DBA\ComparisonFilter;
use DBA\ContainFilter;
use DBA\OrderFilter;
use DBA\JoinFilter;
use DBA\File;
use DBA\FileTask;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\QueryFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::CHUNKS_VIEW_PERM);

Template::loadInstance("chunks");
Menu::get()->setActive("chunks");

$oF = null;
UI::add('all', true);
UI::add('pageTitle', "Chunk Activity");
if (!isset($_GET['show'])) {
  $page = 0;
  $PAGESIZE = 50;
  if (isset($_GET['page'])) {
    $page = intval($_GET['page']);
  }
  UI::add('page', $page);
  $numentries = Factory::getChunkFactory()->countFilter([]);
  UI::add('maxpage', floor($numentries / $PAGESIZE));
  $limit = $page * $PAGESIZE;
  $oF = new OrderFilter(Chunk::SOLVE_TIME, "DESC LIMIT $limit, $PAGESIZE", Factory::getChunkFactory());
  UI::add('all', false);
  UI::add('pageTitle', "Chunks Activity (page " . ($page + 1) . ")");
}

// load groups for user
$qF = new QueryFilter(AccessGroupUser::USER_ID, Login::getInstance()->getUserID(), "=");
$userGroups = Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => $qF]);
$accessGroupIds = array();
foreach ($userGroups as $userGroup) {
  $accessGroupIds[] = $userGroup->getAccessGroupId();
}

$jF1 = new JoinFilter(Factory::getTaskFactory(), Chunk::TASK_ID, Task::TASK_ID);
$jF2 = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_ID, TaskWrapper::TASK_WRAPPER_ID);
$qF1 = new QueryFilter(Task::IS_ARCHIVED, 1, "<>", Factory::getTaskFactory());
$qF2 = new ContainFilter(TaskWrapper::ACCESS_GROUP_ID, $accessGroupIds);
$qF3 = new ComparisonFilter("(SELECT COUNT(*) FROM " 
                              . Factory::getFileTaskFactory()->getModelTable() 
                              . " WHERE " 
                              . Factory::getFileTaskFactory()->getModelTable() . "." . FileTask::TASK_ID 
                              . "=" 
                              . Factory::getTaskFactory()->getModelTable() . "." . Task::TASK_ID 
                              . ")",
                            "(SELECT COUNT(*) FROM "
                              . Factory::getFileFactory()->getModelTable()
                              . " JOIN " 
                              . Factory::getFileTaskFactory()->getModelTable() 
                              . " ON "
                              . Factory::getFileTaskFactory()->getModelTable() . "." . FileTask::FILE_ID 
                              . "="
                              . Factory::getFileFactory()->getModelTable() . "." . File::FILE_ID
                              . " WHERE "
                               . Factory::getFileTaskFactory()->getModelTable() . "." . FileTask::TASK_ID 
                               . "="
                              . Factory::getTaskFactory()->getModelTable() . "." . Task::TASK_ID 
                              . " AND "
                               . Factory::getFileFactory()->getModelTable() . "." . File::ACCESS_GROUP_ID
                                . " IN (" . implode(',', $accessGroupIds) . "))",
                            "=");

if ($oF == null) {
  $joined = Factory::getChunkFactory()->filter([Factory::FILTER => [$qF1, $qF2, $qF3], Factory::JOIN => [$jF1, $jF2]]);
}
else {
  $joined = Factory::getChunkFactory()->filter([Factory::ORDER => $oF, Factory::FILTER => [$qF1, $qF2, $qF3], Factory::JOIN => [$jF1, $jF2]]);
}
/** @var Chunk[] $chunks */
$chunks = $joined[Factory::getChunkFactory()->getModelName()];

$spent = new DataSet();
foreach ($chunks as $chunk) {
  $spent->addValue($chunk->getId(), max($chunk->getDispatchTime(), $chunk->getSolveTime()) - $chunk->getDispatchTime());
}
UI::add('chunks', $chunks);
UI::add('spent', $spent);

$tasks = Factory::getTaskFactory()->filter([]);
$taskNames = new DataSet();
foreach ($tasks as $task) {
  $taskNames->addValue($task->getId(), $task->getTaskName());
}
UI::add('taskNames', $taskNames);

$agents = Factory::getAgentFactory()->filter([]);
$agentNames = new DataSet();
foreach ($agents as $agent) {
  $agentNames->addValue($agent->getId(), $agent->getAgentName());
}
UI::add('agentNames', $agentNames);

echo Template::getInstance()->render(UI::getObjects());




