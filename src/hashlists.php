<?php

use DBA\Chunk;
use DBA\ContainFilter;
use DBA\Hashlist;
use DBA\HashlistHashlist;
use DBA\HashType;
use DBA\JoinFilter;
use DBA\Pretask;
use DBA\QueryFilter;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::HASHLISTS_VIEW_PERM);

$TEMPLATE = new Template("hashlists/index");
$MENU->setActive("lists_norm");
$OBJECTS['zap'] = false;

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $hashlistHandler = new HashlistHandler();
  $hashlistHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0 && !$OBJECTS['zap']) {
    Util::refresh();
  }
}

if (isset($_GET['new']) && $ACCESS_CONTROL->hasPermission(DAccessControl::CREATE_HASHLIST_ACCESS)) {
  //new hashlist
  $MENU->setActive("lists_new");
  $OBJECTS['impfiles'] = Util::scanImportDirectory();
  $hashtypes = Factory::getHashTypeFactory()->filter([]);
  $list = array();
  foreach ($hashtypes as $hashtype) {
    $list[] = $hashtype->getId() . ": " . $hashtype->getIsSalted();
  }
  $allHashtypes = "{" . implode(",", $list) . "}";
  $OBJECTS['allHashtypes'] = $allHashtypes;
  $OBJECTS['hashtypes'] = $hashtypes;
  $OBJECTS['accessGroups'] = AccessUtils::getAccessGroupsOfUser($LOGIN->getUser());
  $TEMPLATE = new Template("hashlists/new");
  $OBJECTS['pageTitle'] = "Add new Hashlist";
}
else if (isset($_GET['id'])) {
  //show hashlist detail page
  $jF = new JoinFilter(Factory::getHashTypeFactory(), HashType::HASH_TYPE_ID, Hashlist::HASH_TYPE_ID);
  $qF = new QueryFilter(Hashlist::HASHLIST_ID, $_GET['id'], "=");
  $joined = Factory::getHashlistFactory()->filter([Factory::JOIN => $jF, Factory::FILTER => $qF]);
  if (sizeof($joined[Factory::getHashlistFactory()->getModelName()]) == 0) {
    UI::printError("ERROR", "Hashlist not found!");
  }
  $list = new DataSet(array('hashlist' => $joined[Factory::getHashlistFactory()->getModelName()][0], 'hashtype' => $joined[Factory::getHashTypeFactory()->getModelName()][0]));
  $OBJECTS['list'] = $list;
  $OBJECTS['accessGroup'] = Factory::getAccessGroupFactory()->get($list->getVal('hashlist')->getAccessGroupId());

  //check if the list is a superhashlist
  $OBJECTS['sublists'] = array();
  if ($list->getVal('hashlist')->getFormat() == DHashlistFormat::SUPERHASHLIST) {
    $jF = new JoinFilter(Factory::getHashlistHashlistFactory(), HashlistHashlist::HASHLIST_ID, Hashlist::HASHLIST_ID);
    $qF = new QueryFilter(HashlistHashlist::PARENT_HASHLIST_ID, $list->getVal('hashlist')->getId(), "=", Factory::getHashlistHashlistFactory());
    $joined = Factory::getHashlistFactory()->filter([Factory::JOIN => $jF, Factory::FILTER => $qF]);
    $sublists = array();
    for ($x = 0; $x < sizeof($joined[Factory::getHashlistFactory()->getModelName()]); $x++) {
      $sublists[] = new DataSet(array('hashlist' => $joined[Factory::getHashlistFactory()->getModelName()][$x], 'superhashlist' => $joined[Factory::getHashlistHashlistFactory()->getModelName()][$x]));
    }
    $OBJECTS['sublists'] = $sublists;
  }

  //load tasks assigned to hashlist
  $qF = new QueryFilter(TaskWrapper::HASHLIST_ID, $list->getVal('hashlist')->getId(), "=", Factory::getTaskWrapperFactory());
  $jF = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID);
  $joined = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
  /** @var $tasks Task[] */
  $tasks = $joined[Factory::getTaskFactory()->getModelName()];
  $hashlistTasks = array();
  foreach ($tasks as $task) {
    $isActive = false;
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
    $sum = array('dispatched' => $task->getKeyspaceProgress(), 'searched' => 0, 'cracked' => 0);
    foreach ($chunks as $chunk) {
      $sum['searched'] += $chunk->getProgress();
      $sum['cracked'] += $chunk->getCracked();
      if (time() - SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT) < max($chunk->getDispatchTime(), $chunk->getSolveTime())) {
        $isActive = true;
      }
    }
    $set = new DataSet(array('task' => $task, 'isActive' => $isActive, 'searched' => $sum['searched'], 'dispatched' => $sum['dispatched'], 'cracked' => $sum['cracked']));
    $hashlistTasks[] = $set;
  }
  $OBJECTS['tasks'] = $hashlistTasks;

  //load list of available preconfigured tasks
  if (SConfig::getInstance()->getVal(DConfig::HIDE_IMPORT_MASKS) == 1) {
    $qF = new QueryFilter(Pretask::IS_MASK_IMPORT, 0, "=");
    $OBJECTS['preTasks'] = Factory::getPretaskFactory()->filter([Factory::FILTER => $qF]);
  }
  else{
    $OBJECTS['preTasks'] = Factory::getPretaskFactory()->filter([]);
  }

  // load list of available supertasks
  $OBJECTS['superTasks'] = Factory::getSupertaskFactory()->filter([]);

  $TEMPLATE = new Template("hashlists/detail");
  $OBJECTS['pageTitle'] = "Hashlist details for " . $list->getVal('hashlist')->getHashlistName();
}
else {
  //load all hashlists
  $jF = new JoinFilter(Factory::getHashTypeFactory(), HashType::HASH_TYPE_ID, Hashlist::HASH_TYPE_ID);
  $qF1 = new QueryFilter(Hashlist::FORMAT, "" . DHashlistFormat::SUPERHASHLIST, "<>", Factory::getHashlistFactory());
  $qF2 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($LOGIN->getUser())));
  $joinedHashlists = Factory::getHashlistFactory()->filter([Factory::JOIN => $jF, Factory::FILTER => [$qF1, $qF2]]);
  $hashlists = array();
  for ($x = 0; $x < sizeof($joinedHashlists[Factory::getHashlistFactory()->getModelName()]); $x++) {
    $hashlists[] = new DataSet(array('hashlist' => $joinedHashlists[Factory::getHashlistFactory()->getModelName()][$x], 'hashtype' => $joinedHashlists[Factory::getHashTypeFactory()->getModelName()][$x]));
  }
  $OBJECTS['hashlists'] = $hashlists;
  $OBJECTS['numHashlists'] = sizeof($hashlists);
  $OBJECTS['pageTitle'] = "Hashlists";
}

echo $TEMPLATE->render($OBJECTS);




