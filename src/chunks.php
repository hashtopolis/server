<?php

use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\Menu;
use Hashtopolis\inc\templating\Template;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\utils\AccessControl;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

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
  $oF = new OrderFilter(Chunk::SOLVE_TIME, "DESC LIMIT $PAGESIZE OFFSET $limit", Factory::getChunkFactory());
  UI::add('all', false);
  UI::add('pageTitle', "Chunks Activity (page " . ($page + 1) . ")");
}

$accessGroupIds = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser()));
$jF1 = new JoinFilter(Factory::getTaskFactory(), Chunk::TASK_ID, Task::TASK_ID);
$jF2 = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskFactory());
$jF3 = new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory());
$qF1 = new QueryFilter(Task::IS_ARCHIVED, 1, "<>", Factory::getTaskFactory());
$qF2 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroupIds, Factory::getHashlistFactory());

if ($oF == null) {
  $joined = Factory::getChunkFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::JOIN => [$jF1, $jF2, $jF3]]);
}
else {
  $joined = Factory::getChunkFactory()->filter([Factory::ORDER => $oF, Factory::FILTER => [$qF1, $qF2], Factory::JOIN => [$jF1, $jF2, $jF3]]);
}
/** @var Chunk[] $chunks */
$chunks = $joined[Factory::getChunkFactory()->getModelName()];

$spent = new DataSet();
foreach ($chunks as $chunk) {
  $spent->addValue($chunk->getId(), max($chunk->getDispatchTime(), $chunk->getSolveTime()) - $chunk->getDispatchTime());
}
UI::add('chunks', $chunks);
UI::add('spent', $spent);

$tasks = $joined[Factory::getTaskFactory()->getModelName()];
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




