<?php

use DBA\Chunk;
use DBA\Hashlist;
use DBA\HashType;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\SuperHashlistHashlist;
use DBA\Task;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */
/** @var DataSet $CONFIG */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::READ_ONLY) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("hashlists/index");
$MENU->setActive("lists_norm");

//catch actions here...
if (isset($_POST['action'])) {
  $hashlistHandler = new HashlistHandler();
  $hashlistHandler->handle($_POST['action']);
}

if(isset($_GET['new'])){
  //new hashlist
  $MENU->setActive("lists_new");
  $OBJECTS['impfiles'] = Util::scanImportDirectory();
  $OBJECTS['hashtypes'] = $FACTORIES::getHashTypeFactory()->filter(array());
  $TEMPLATE = new Template("hashlists/new");
}
else if (isset($_GET['id'])) {
  //show hashlist detail page
  $jF = new JoinFilter($FACTORIES::getHashTypeFactory(), HashType::HASH_TYPE_ID, Hashlist::HASH_TYPE_ID);
  $qF = new QueryFilter(Hashlist::HASHLIST_ID, $_GET['id'], "=");
  $joined = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::JOIN => array($jF), $FACTORIES::FILTER => array($qF)));
  if(sizeof($joined['Hashlist']) == 0){
    UI::printError("ERROR", "Hashlist not found!");
  }
  $list = new DataSet(array('hashlist' => $joined['Hashlist'][0], 'hashtype' => $joined['HashType'][0]));
  $OBJECTS['list'] = $list;
  
  //check if the list is a superhashlist
  if ($list->getVal('hashlist')->getFormat() == 3) {
    $jF = new JoinFilter($FACTORIES::getSuperHashlistHashlistFactory(), SuperHashlistHashlist::HASHLIST_ID, Hashlist::HASHLIST_ID);
    $qF = new QueryFilter(SuperHashlistHashlist::SUPER_HASHLIST_ID, $list->getVal('hashlist')->getId(), "=", $FACTORIES::getSuperHashlistHashlistFactory());
    $joined = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::JOIN => array($jF), $FACTORIES::FILTER => array($qF)));
    $sublists = array();
    for($x=0;$x<sizeof($joined['Hashlist']);$x++){
      $sublists[] = new DataSet(array('hashlist' => $joined['Hashlist'][$x], 'superhashlist' => $joined['SuperHashlist'][$x]));
    }
    $OBJECTS['sublists'] = $sublists;
  }
  
  //load tasks assigned to hashlist
  $qF = new QueryFilter(Task::HASHLIST_ID, $list->getVal('hashlist')->getId(), "=");
  $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => array($qF)));
  $hashlistTasks = array();
  foreach($tasks as $task){
    $isActive = false;
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => array($qF)));
    $sum = array('dispatched' => $task->getProgress(), 'searched' => 0, 'cracked' => 0);
    foreach($chunks as $chunk){
      $sum['searched'] += $chunk->getProgress();
      $sum['cracked'] += $chunk->getCracked();
      if(time() - $CONFIG->getVal(DConfig::CHUNK_TIMEOUT) < max($chunk->getDispatchTime(), $chunk->getSolveTime())){
        $isActive = true;
      }
    }
    $set = new DataSet(array('task' => $task, 'isActive' => $isActive, 'searched' => $sum['searched'], 'dispatched' => $sum['dispatched'], 'cracked' =>$sum['cracked']));
    $hashlistTasks[] = $set;
  }
  $OBJECTS['tasks'] = $hashlistTasks;
  
  //load list of available preconfigured tasks
  $qF = new QueryFilter(Task::HASHLIST_ID, null, "=");
  $preTasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => array($qF)));
  $OBJECTS['preTasks'] = $preTasks;
  $TEMPLATE = new Template("hashlists/detail");
}
else {
  //load all hashlists
  $jF = new JoinFilter($FACTORIES::getHashTypeFactory(), HashType::HASH_TYPE_ID, Hashlist::HASH_TYPE_ID);
  $qF = new QueryFilter(Hashlist::FORMAT, "" . DHashlistFormat::SUPERHASHLIST, "<>", $FACTORIES::getHashlistFactory());
  $joinedHashlists = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::JOIN => $jF, $FACTORIES::FILTER => $qF));
  $hashlists = array();
  for($x=0;$x<sizeof($joinedHashlists['Hashlist']);$x++){
    $hashlists[] = new DataSet(array('hashlist' => $joinedHashlists['Hashlist'][$x], 'hashtype' => $joinedHashlists['HashType'][$x]));
  }
  $OBJECTS['hashlists'] = $hashlists;
  $OBJECTS['numHashlists'] = sizeof($hashlists);
}

echo $TEMPLATE->render($OBJECTS);




