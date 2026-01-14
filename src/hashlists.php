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

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::HASHLISTS_VIEW_PERM);

Template::loadInstance("hashlists/index");
Menu::get()->setActive("lists_norm");
UI::add('zap', false);

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $hashlistHandler = new HashlistHandler();
  $hashlistHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0 && !UI::get('zap')) {
    Util::refresh();
  }
}

if (isset($_GET['new']) && AccessControl::getInstance()->hasPermission(DAccessControl::CREATE_HASHLIST_ACCESS)) {
  //new hashlist
  Menu::get()->setActive("lists_new");
  UI::add('impfiles', Util::scanImportDirectory());
  $hashtypes = Factory::getHashTypeFactory()->filter([]);
  $list = array();
  foreach ($hashtypes as $hashtype) {
    $list[] = $hashtype->getId() . ": " . $hashtype->getIsSalted();
  }
  $allHashtypes = "{" . implode(",", $list) . "}";
  UI::add('allHashtypes', $allHashtypes);
  UI::add('hashtypes', $hashtypes);
  UI::add('accessGroups', AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser()));
  UI::add('pageTitle', "Add new Hashlist");
  Template::loadInstance("hashlists/new");
}
else if (isset($_GET['id'])) {
  //show hashlist detail page
  $jF = new JoinFilter(Factory::getHashTypeFactory(), HashType::HASH_TYPE_ID, Hashlist::HASH_TYPE_ID);
  $qF = new QueryFilter(Hashlist::HASHLIST_ID, $_GET['id'], "=");
  $joined = Factory::getHashlistFactory()->filter([Factory::JOIN => $jF, Factory::FILTER => $qF]);
  /** @var $hashlists Hashlist[] */
  $hashlists = $joined[Factory::getHashlistFactory()->getModelName()];
  if (sizeof($hashlists) == 0) {
    UI::printError("ERROR", "Hashlist not found!");
  } else if (!AccessUtils::userCanAccessHashlists($hashlists, Login::getInstance()->getUser())) {
    UI::printError("ERROR", "No access to this hashlist!");
  }
  $list = new DataSet(array('hashlist' => $hashlists[0], 'hashtype' => $joined[Factory::getHashTypeFactory()->getModelName()][0]));
  UI::add('list', $list);
  UI::add('accessGroup', Factory::getAccessGroupFactory()->get($list->getVal('hashlist')->getAccessGroupId()));
  UI::add('accessGroups', AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser()));

  //check if the list is a superhashlist
  UI::add('sublists', []);
  if ($list->getVal('hashlist')->getFormat() == DHashlistFormat::SUPERHASHLIST) {
    $jF = new JoinFilter(Factory::getHashlistHashlistFactory(), HashlistHashlist::HASHLIST_ID, Hashlist::HASHLIST_ID);
    $qF = new QueryFilter(HashlistHashlist::PARENT_HASHLIST_ID, $list->getVal('hashlist')->getId(), "=", Factory::getHashlistHashlistFactory());
    $joined = Factory::getHashlistFactory()->filter([Factory::JOIN => $jF, Factory::FILTER => $qF]);
    $sublists = array();
    /** @var $hashlists Hashlist[] */
    $hashlists = $joined[Factory::getHashlistFactory()->getModelName()];
    for ($x = 0; $x < sizeof($hashlists); $x++) {
      $sublists[] = new DataSet(['hashlist' => $hashlists[$x], 'superhashlist' => $joined[Factory::getHashlistHashlistFactory()->getModelName()][$x]]);
    }
    UI::add('sublists', $sublists);
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
    $sum = ['dispatched' => $task->getKeyspaceProgress(), 'searched' => 0, 'cracked' => 0];
    foreach ($chunks as $chunk) {
      $sum['searched'] += $chunk->getCheckpoint() - $chunk->getSkip();
      $sum['cracked'] += $chunk->getCracked();
      if (time() - SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT) < max($chunk->getDispatchTime(), $chunk->getSolveTime())) {
        $isActive = true;
      }
    }
    $set = new DataSet(['task' => $task, 'isActive' => $isActive, 'searched' => $sum['searched'], 'dispatched' => $sum['dispatched'], 'cracked' => $sum['cracked']]);
    $hashlistTasks[] = $set;
  }
  UI::add('tasks', $hashlistTasks);

  //load list of available preconfigured tasks
  if (SConfig::getInstance()->getVal(DConfig::HIDE_IMPORT_MASKS) == 1) {
    $qF = new QueryFilter(Pretask::IS_MASK_IMPORT, 0, "=");
    UI::add('preTasks', Factory::getPretaskFactory()->filter([Factory::FILTER => $qF]));
  }
  else {
    UI::add('preTasks', Factory::getPretaskFactory()->filter([]));
  }

  // load list of available supertasks
  UI::add('superTasks', Factory::getSupertaskFactory()->filter([]));

  // load binaries and versions for supertask list
  UI::add('binaries', Factory::getCrackerBinaryTypeFactory()->filter([]));
  $versions = Factory::getCrackerBinaryFactory()->filter([]);
  usort($versions, ["Util", "versionComparisonBinary"]);
  UI::add('versions', $versions);

  UI::add('pageTitle', "Hashlist details for " . $list->getVal('hashlist')->getHashlistName());
  Template::loadInstance("hashlists/detail");
}
else {
  $archived = 0;
  UI::add('showArchived', false);
  UI::add('pageTitle', "Hashlists");
  if (isset($_GET['archived']) && $_GET['archived'] == 'true') {
    $archived = 1;
    UI::add('showArchived', true);
    UI::add('pageTitle', "Archived Hashlists");
  }
  //load all hashlists
  $jF = new JoinFilter(Factory::getHashTypeFactory(), HashType::HASH_TYPE_ID, Hashlist::HASH_TYPE_ID);
  $qF1 = new QueryFilter(Hashlist::FORMAT, "" . DHashlistFormat::SUPERHASHLIST, "<>", Factory::getHashlistFactory());
  $qF2 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser())));
  $qF3 = new QueryFilter(Hashlist::IS_ARCHIVED, $archived, "=");
  $joined = Factory::getHashlistFactory()->filter([Factory::JOIN => $jF, Factory::FILTER => [$qF1, $qF2, $qF3]]);
  /** @var $joinedHashlists Hashlist[] */
  $joinedHashlists = $joined[Factory::getHashlistFactory()->getModelName()];
  $hashlists = array();
  for ($x = 0; $x < sizeof($joinedHashlists); $x++) {
    $hashlists[] = new DataSet(['hashlist' => $joinedHashlists[$x], 'hashtype' => $joined[Factory::getHashTypeFactory()->getModelName()][$x]]);
  }
  UI::add('hashlists', $hashlists);
  UI::add('numHashlists', sizeof($hashlists));
}

echo Template::getInstance()->render(UI::getObjects());
