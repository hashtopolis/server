<?php
require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}

$TEMPLATE = new Template("tasks/index");
$MENU->setActive("tasks_list");

//catch agents actions here...
if (isset($_POST['action'])) {
  $taskHandler = new TaskHandler();
  $taskHandler->handle($_POST['action']);
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
$OBJECTS['autorefresh'] = $autorefresh;

if (isset($_GET['id'])) {
  if($LOGIN->getLevel() < 5){
    $TEMPLATE = new Template("restricted");
    die($TEMPLATE->render($OBJECTS));
  }
  
  $TEMPLATE = new Template("tasks/detail");
  $task = $FACTORIES::getTaskFactory()->get($_GET['id']);
  if($task == null){
    UI::printError("ERROR", "Invalid task ID!");
  }
  $OBJECTS['task'] = $task;
  
  if($task->getHashlistId() != null) {
    $hashlist = $FACTORIES::getHashlistFactory()->get($task->getHashlistId());
    $OBJECTS['hashlist'] = $hashlist;
    $hashtype = $FACTORIES::getHashTypeFactory()->get($hashlist->getHashtypeId());
    $OBJECTS['hashtype'] = $hashtype;
  }
  
  $isActive = 0;
  $activeChunks = array();
  $activeChunksIds = new DataSet();
  $qF = new QueryFilter("taskId", $task->getId(), "=", $FACTORIES::getChunkFactory());
  $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF));
  $activeAgents = new DataSet();
  foreach($chunks as $chunk){
    $activeAgents->addValue($chunk->getAgentId(), false);
    if(time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) < $CONFIG->getVal('chunktimeout')){
      $isActive = 1;
      $activeChunks[] = $chunk;
      $activeChunksIds->addValue($chunk->getId(), true);
      $activeAgents->addValue($chunk->getAgentId(), true);
    }
    else{
      $activeChunksIds->addValue($chunk->getId(), false);
    }
  }
  $OBJECTS['isActive'] = $isActive;
  
  $agentsBench = new DataSet();
  $agentsSpeed = new DataSet();
  $qF = new QueryFilter("taskId", $task->getId(), "=");
  $assignments = $FACTORIES::getAssignmentFactory()->filter(array('filter' => $qF));
  foreach($assignments as $assignment) {
    $agentsBench->addValue($chunk->getAgentId(), $joinedChunks['Assignment'][$i]->getBenchmark());
    $agentsSpeed->addValue($chunk->getAgentId(), $joinedChunks['Assignment'][$i]->getSpeed());
  }
  
  $cProgress = 0;
  $chunkIntervals = array();
  $agentsProgress = new DataSet();
  $agentsSpent = new DataSet();
  $agentsCracked = new DataSet();
  $qF = new QueryFilter("taskId", $task->getId(), "=");
  $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF));
  foreach($chunks as $chunk){
    $chunkIntervals[] = array("start" => $chunk->getDispatchTime(), "stop" => $chunk->getSolveTime());
    $cProgress += $chunk->getProgress();
    if(!$agentsProgress->getVal($chunk->getAgentId())){
      $agentsProgress->addValue($chunk->getAgentId(), $chunk->getProgress());
      $agentsCracked->addValue($chunk->getAgentId(), $chunk->getCracked());
      $agentsSpent->addValue($chunk->getAgentId(), max($chunk->getSolveTime() - $chunk->getDispatchTime(), 0));
    }
    else{
      $agentsProgress->addValue($chunk->getAgentId(), $agentsProgress->getVal($chunk->getAgentId()) + $chunk->getProgress());
      $agentsCracked->addValue($chunk->getAgentId(), $agentsCracked->getVal($chunk->getAgentId()) + $chunk->getCracked());
      $agentsSpent->addValue($chunk->getAgentId(), $agentsSpent->getVal($chunk->getAgentId()) + max($chunk->getSolveTime() - $chunk->getDispatchTime(), 0));
    }
  }
  $OBJECTS['agentsProgress'] = $agentsProgress;
  $OBJECTS['agentsSpent'] = $agentsSpent;
  $OBJECTS['agentsCracked'] = $agentsCracked;
  $OBJECTS['cProgress'] = $cProgress;
  
  $currentSpeed = 0;
  for($i=0;$i<sizeof($joinedChunks['Chunk']);$i++){
    $chunk = $joinedChunks['Chunk'][$i];
    if(time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) < time() - $CONFIG->getVal('chunktimeout') && $chunk->getRprogress() < 10000){
      $currentSpeed += $joinedChunks['Assignment'][$i]->getSpeed();
    }
  }
  $OBJECTS['currentSpeed'] = $currentSpeed;
  
  $timeSpent = 0;
  for($i=1;$i<=sizeof($chunkIntervals);$i++){
    if (isset($chunkIntervals[$i]) && $chunkIntervals[$i]["start"] <= $chunkIntervals[$i - 1]["stop"]) {
      $chunkIntervals[$i]["start"] = $chunkIntervals[$i - 1]["start"];
      if ($chunkIntervals[$i]["stop"] < $chunkIntervals[$i - 1]["stop"]) {
        $chunkIntervals[$i]["stop"] = $chunkIntervals[$i - 1]["stop"];
      }
    }
    else {
      $timeSpent += ($chunkIntervals[$i - 1]["stop"] - $chunkIntervals[$i - 1]["start"]);
    }
  }
  $OBJECTS['timeSpent'] = $timeSpent;
  if($task->getKeyspace() != 0 && ($cProgress/$task->getKeyspace()) != 0) {
    $OBJECTS['timeLeft'] = round($timeSpent / ($cProgress / $task->getKeyspace()));
  }
  else{
    $OBJECTS['timeLeft'] = -1;
  }
  
  $qF = new QueryFilter("taskId", $task->getId(), "=", $FACTORIES::getTaskFileFactory());
  $jF = new JoinFilter($FACTORIES::getTaskFileFactory(), "fileId", "fileId");
  $joinedFiles = $FACTORIES::getFileFactory()->filter(array('filter' => $qF, 'join' => $jF));
  $OBJECTS['attachedFiles'] = $joinedFiles['File'];
  
  $jF = new JoinFilter($FACTORIES::getAssignmentFactory(), "agentId", "agentId");
  $qF = new QueryFilter("taskId", $task->getId(), "=", $FACTORIES::getAssignmentFactory());
  $joinedAgents = $FACTORIES::getAgentFactory()->filter(array('filter' => $qF, 'join' => $jF));
  $assignedAgents = array();
  foreach($joinedAgents['Agent'] as $agent){
    $assignedAgents[] = $agent->getId();
  }
  $OBJECTS['agents'] = $joinedAgents['Agent'];
  $OBJECTS['activeAgents'] = $activeAgents;
  $OBJECTS['agentsBench'] = $agentsBench;
  $OBJECTS['agentsSpeed'] = $agentsSpeed;
  
  $assignAgents = array();
  $allAgents = $FACTORIES::getAgentFactory()->filter(array());
  foreach($allAgents as $agent){
    if(!in_array($agent->getId(), $assignedAgents)){
      $assignAgents[] = $agent;
    }
  }
  $OBJECTS['assignAgents'] = $assignAgents;
  
  if(isset($_GET['allagents'])){
    $OBJECTS['showAllAgents'] = true;
    $allAgentsSpent = new DataSet();
    $allAgents = new DataSet();
    $agentObjects = array();
    $qF = new QueryFilter("taskId", $task->getId(), "=", $FACTORIES::getChunkFactory());
    $jF = new JoinFilter($FACTORIES::getChunkFactory(), "agentId", "agentId");
    $joinedAgents = $FACTORIES::getAgentFactory()->filter(array('filter' => $qF, 'join' => $jF));
    for($i=0;$i<sizeof($joinedAgents['Agent']);$i++){
      $chunk = $joinedAgents['Chunk'][$i];
      $agent = $joinedAgents['Agent'][$i];
      if($allAgents->getVal($agent->getId()) == null){
        $allAgents->addValue($agent->getId(), $agent);
        $agentObjects[] = $agent;
      }
      if($chunk->getSolveTime() > $chunk->getDispatchTime()){
        if($allAgentsSpent->getVal($agent->getId()) == null){
          $allAgentsSpent->addValue($agent->getId(), $chunk->getSolveTime() - $chunk->getDispatchTime());
        }
        else{
          $allAgentsSpent->addValue($agent->getId(), $allAgentsSpent->getVal($agent->getId()) + $chunk->getSolveTime() - $chunk->getDispatchTime());
        }
      }
    }
    $OBJECTS['agentObjects'] = $agentObjects;
    $OBJECTS['allAgentsSpent'] = $allAgentsSpent;
  }
  
  if(isset($_GET['all'])){
    $OBJECTS['chunkFilter'] = 1;
    $qF = new QueryFilter("taskId", $task->getId(), "=");
    $oF = new OrderFilter("solveTime", "DESC LIMIT 100");
    $OBJECTS['chunks'] = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF, 'order' => $oF));
    $OBJECTS['activeChunks'] = $activeChunksIds;
  }
  else{
    $OBJECTS['chunkFilter'] = 0;
    $OBJECTS['chunks'] = $activeChunks;
    $OBJECTS['activeChunks'] = $activeChunksIds;
  }
  
  $agents = $FACTORIES::getAgentFactory()->filter(array());
  $fullAgents = new DataSet();
  foreach($agents as $agent){
    $fullAgents->addValue($agent->getId(), $agent);
  }
  $OBJECTS['fullAgents'] = $fullAgents;
}
else if (isset($_GET['new'])) {
  if($LOGIN->getLevel() < 5){
    $TEMPLATE = new Template("restricted");
    die($TEMPLATE->render($OBJECTS));
  }
  $TEMPLATE = new Template("tasks/new");
  $MENU->setActive("tasks_new");
  $orig = 0;
  $copy = new Task(0, "", "", null, $CONFIG->getVal("chunktime"), $CONFIG->getVal("statustimer"), 0, 0, 0, 0, "", 0, 0);
  if (isset($_POST["copy"])) {
    //copied from a task
    $copy = $FACTORIES::getTaskFactory()->get($_POST['copy']);
    if($copy != null){
      $orig = $copy->getId();
      $copy->setId(0);
      $copy->setTaskName($copy->getTaskName() . " (copy)");
    }
  }
  $OBJECTS['orig'] = $orig;
  $OBJECTS['copy'] = $copy;
  
  $lists = array();
  $set = new DataSet();
  $set->addValue('id', null);
  $set->addValue("name", "(pre-configured task)");
  $lists[] = $set;
  $res = $FACTORIES::getHashlistFactory()->filter(array());
  foreach ($res as $list) {
    $set = new DataSet();
    $set->addValue('id', $list->getId());
    $set->addValue('name', $list->getHashlistName());
    $lists[] = $set;
  }
  $OBJECTS['lists'] = $lists;
  
  $origFiles = array();
  if($orig > 0){
    $qF = new QueryFilter("taskId", $orig, "=");
    $ff = $FACTORIES::getTaskFileFactory()->filter(array('filter' => $qF));
    foreach($ff as $f) {
      $origFiles[] = $f->getFileId();
    }
  }
  $oF = new OrderFilter("filename", "ASC");
  $allFiles = $FACTORIES::getFileFactory()->filter(array('order' => $oF));
  $rules = array();
  $wordlists = array();
  foreach($allFiles as $singleFile){
    $set = new DataSet();
    $checked = "0";
    if(in_array($singleFile->getId(), $origFiles)){
       $checked = "1";
    }
    $set->addValue('checked', $checked);
    $set->addValue('file', $singleFile);
    if($singleFile->getFileType() == 1){
      $rules[] = $set;
    }
    else{
      $wordlists[] = $set;
    }
  }
  $OBJECTS['wordlists'] = $wordlists;
  $OBJECTS['rules'] = $rules;
}
else {
  $jF = new JoinFilter($FACTORIES::getHashlistFactory(), "hashlistId", "hashlistId");
  $oF1 = new OrderFilter("priority", "DESC");
  $oF2 = new OrderFilter("taskId", "ASC");
  $joinedTasks = $FACTORIES::getTaskFactory()->filter(array('join' => $jF, 'order' => array($oF1, $oF2)));
  $tasks = array();
  for($z=0;$z<sizeof($joinedTasks['Task']);$z++){
    $set = new DataSet();
    $set->addValue('Task', $joinedTasks['Task'][$z]);
    $set->addValue('Hashlist', $joinedTasks['Hashlist'][$z]);
    
    $task = $joinedTasks['Task'][$z];
    $qF = new QueryFilter("taskId", $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array('filter'=> $qF));
    $progress = 0;
    $cracked = 0;
    $maxTime = 0;
    foreach($chunks as $chunk){
      $progress += $chunk->getProgress();
      $cracked += $chunk->getCracked();
      if($chunk->getDispatchTime() > $maxTime){
        $maxTime = $chunk->getDispatchTime();
      }
      if($chunk->getSolveTime() > $maxTime){
        $maxTime = $chunk->getSolveTime();
      }
    }
    
    $isActive = false;
    if(time() - $maxTime < $CONFIG->getVal('chunktimeout')){
      $isActive = true;
    }
    
    $qF = new QueryFilter("taskId", $task->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array('filter' => $qF));
    
    $qF = new QueryFilter("taskId", $task->getId(), "=", $FACTORIES::getTaskFileFactory());
    $jF = new JoinFilter($FACTORIES::getTaskFileFactory(), "fileId", "fileId");
    $joinedFiles = $FACTORIES::getFileFactory()->filter(array('filter' => $qF, 'join' => $jF));
    $sizes = 0;
    $secret = false;
    for($x=0;$x<sizeof($joinedFiles['File']);$x++){
      $sizes += $joinedFiles['File'][$x]->getSize();
      if($joinedFiles['File'][$x]->getSecret() == '1'){
        $secret = true;
      }
    }
    
    $set->addValue('numFiles', sizeof($joinedFiles['File']));
    $set->addValue('filesSize', $sizes);
    $set->addValue('fileSecret', $secret);
    $set->addValue('numAssignments', sizeof($assignments));
    $set->addValue('isActive', $isActive);
    $set->addValue('sumprog', $progress);
    $set->addValue('cracked', $cracked);
    $set->addValue('numChunks', sizeof($chunks));
    
    $tasks[] = $set;
  }
  $OBJECTS['tasks'] = $tasks;
}

echo $TEMPLATE->render($OBJECTS);




