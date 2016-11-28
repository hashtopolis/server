<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 5) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("hashlists/index");
$MENU->setActive("lists_norm");

//catch agents actions here...
if (isset($_POST['action'])) {
  switch ($_POST['action']) {
    //TODO: handler needs to be done + add new hashlists handling there
    
  }
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
  $jF = new JoinFilter($FACTORIES::getHashTypeFactory(), "hashtypeId", "hashtypeId");
  $qF = new QueryFilter("hashlistId", $_GET['id'], "=");
  $joined = $FACTORIES::getHashlistFactory()->filter(array('join' => array($jF), 'filter' => array($qF)));
  if(sizeof($joined['Hashlist']) == 0){
    UI::printError("ERROR", "Hashlist not found!");
  }
  $list = new DataSet(array('hashlist' => $joined['Hashlist'][0], 'hashtype' => $joined['HashType'][0]));
  $OBJECTS['list'] = $list;
  
  //check if the list is a superhashlist
  if ($list->getVal('hashlist')->getFormat() == 3) {
    $jF = new JoinFilter($FACTORIES::getSuperHashlistHashlistFactory(), "hashlistId", "hashlistId");
    $qF = new QueryFilter("superhashlistId", $list->getVal('hashlist')->getId(), "=");
    $joined = $FACTORIES::getHashlistFactory()->filter(array('join' => array($jF), 'filter' => array($qF)));
    $sublists = array();
    for($x=0;$x<sizeof($joined['Hashlist']);$x++){
      $sublists[] = new DataSet(array('hashlist' => $joined['Hashlist'][$x], 'superhashlist' => $joined['SuperHashlist'][$x]));
    }
    $OBJECTS['sublists'] = $sublists;
  }
  
  //load tasks assigned to hashlist
  $qF = new QueryFilter("hashlistId", $list->getVal('hashlist')->getId(), "=");
  $tasks = $FACTORIES::getTaskFactory()->filter(array('filter' => array($qF)));
  $hashlistTasks = array();
  foreach($tasks as $task){
    $isActive = false;
    $qF = new QueryFilter("taskId", $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => array($qF)));
    $sum = array('dispatched' => $task->getProgress(), 'searched' => 0, 'cracked' => 0);
    foreach($chunks as $chunk){
      $sum['searched'] += $chunk->getProgress();
      $sum['cracked'] += $chunk->getCracked();
      if(time() - $CONFIG->getVal('chunktimeout') < max($chunk->getDispatchTime(), $chunk->getSolveTime())){
        $isActive = true;
      }
    }
    $set = new DataSet(array('task' => $task, 'isActive' => $isActive, 'searched' => $sum['searched'], 'dispatched' => $sum['dispatched'], 'cracked' =>$sum['cracked']));
    $hashlistTasks[] = $set;
  }
  $OBJECTS['tasks'] = $hashlistTasks;
  
  //load list of available preconfigured tasks
  $qF = new QueryFilter("hashlistId", "0", "=");
  $preTasks = $FACTORIES::getTaskFactory()->filter(array('filter' => array($qF)));
  $OBJECTS['preTasks'] = $preTasks;
  $TEMPLATE = new Template("hashlists/detail");
}
else {
  //load all hashlists
  $jF = new JoinFilter($FACTORIES::getHashTypeFactory(), "hashtypeId", "hashtypeId");
  $joinedHashlists = $FACTORIES::getHashlistFactory()->filter(array('join' => array($jF)));
  $hashlists = array();
  for($x=0;$x<sizeof($joinedHashlists['Hashlist']);$x++){
    $hashlists[] = new DataSet(array('hashlist' => $joinedHashlists['Hashlist'][$x], 'hashtype' => $joinedHashlists['HashType'][$x]));
  }
  $OBJECTS['hashlists'] = $hashlists;
  $OBJECTS['numHashlists'] = sizeof($hashlists);
}

echo $TEMPLATE->render($OBJECTS);




