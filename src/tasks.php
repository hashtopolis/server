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
  $jF = new JoinFilter($FACTORIES::getAssignmentFactory(), "taskId", "taskId");
  $joinedChunks = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF, 'join' => $jF));
  $activeAgents = new DataSet();
  $agentsBench = new DataSet();
  $agentsSpeed = new DataSet();
  for($i=0;$i<sizeof($joinedChunks['Chunk']);$i++){
    $chunk = $joinedChunks['Chunk'][$i];
    $activeAgents->addValue($chunk->getAgentId(), "0");
    $agentsBench->addValue($chunk->getAgentId(), $joinedChunks['Assignment'][$i]->getBenchmark());
    $agentsSpeed->addValue($chunk->getAgentId(), $joinedChunks['Assignment'][$i]->getSpeed());
    if(time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) < time() - $CONFIG->getVal('chunktimeout')){
      $isActive = 1;
      $activeChunks[] = $chunk;
      $activeChunksIds->addValue($chunk->getId(), "1");
      $activeAgents->addValue($chunk->getAgentId(), "1");
    }
    else{
      $activeChunksIds->addValue($chunk->getId(), "0");
    }
  }
  $OBJECTS['isActive'] = $isActive;
  
  $cProgress = 0;
  $chunkIntervals = array();
  $agentsProgress = new DataSet();
  $agentsSpent = new DataSet();
  $agentsCracked = new DataSet();
  $qF = new QueryFilter("taskId", $task->getId(), "=");
  $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF));
  foreach($chunks as $chunk){
    $chunkIntervals[] = array("start" => $chunk->getDispatchTime(), "stop" => getSolveTime());
    $cProgress += $chunk->getProgress();
    if(!$agentsProgress->getVal($chunk->getAgentId())){
      $agentsProgress->addValue($chunk->getAgentId(), $chunk->getProgress());
      $agentsCracked->addValue($chunk->getAgentId(), $chunk->getCracked());
      $agentsSpent->addValue($chunk->getAgentId(), min($chunk->getSolveTime() - $chunk->getDispatchTime(), 0));
    }
    else{
      $agentsProgress->addValue($chunk->getAgentId(), $agentsProgress->getVal($chunk->getAgentId()) + $chunk->getProgress());
      $agentsCracked->addValue($chunk->getAgentId(), $agentsCracked->getVal($chunk->getAgentId()) + $chunk->getCracked());
      $agentsSpent->addValue($chunk->getAgentId(), $agentsSpent->getVal($chunk->getAgentId()) + min($chunk->getSolveTime() - $chunk->getDispatchTime(), 0));
    }
  }
  $OBJECTS['agentsProgress'] = $agentsProgress;
  $OBJECTS['agentsSpent'] = $agentsSpent;
  $OBJECTS['agentsCracked'] = $agentsCracked;
  $OBJECTS['cProgress'] = $cProgress;
  
  $currentSpeed = 0;
  for($i=0;$i<sizeof($joinedChunks['Chunk']);$i++){
    $chunk = $joinedChunks['Chunk'][$i];
    if(time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) < time() - $CONFIG->getVal('chunktimeout')){
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
    $fullAgents->addValue($agent->getId(), $agents);
  }
  $OBJECTS['fullAgents'] = $fullAgents;
  
  // show task details
  /*$taskSet = new DataSet();
  $DB = $FACTORIES::getagentsFactory()->getDB();
  $task = intval($_GET["id"]);
  $filter = intval(isset($_GET["all"]) ? $_GET["all"] : "");
  
  $res = $DB->query("SELECT tasks.*,hashlists.name AS hname,hashlists.format,hashlists.hashtype AS htype,hashtypes.description AS htypename,ROUND(chunks.cprogress) AS cprogress,SUM(assignments.speed*IFNULL(achunks.working,0)) AS taskspeed,IF(chunks.lastact>" . (time() - $CONFIG->getVal('chunktimeout')) . ",1,0) AS active FROM tasks LEFT JOIN hashlists ON hashlists.id=tasks.hashlist LEFT JOIN hashtypes ON hashlists.hashtype=hashtypes.id LEFT JOIN (SELECT task,SUM(progress) AS cprogress,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks GROUP BY task) chunks ON chunks.task=tasks.id LEFT JOIN assignments ON assignments.task=tasks.id LEFT JOIN (SELECT DISTINCT agent,1 AS working FROM chunks WHERE task=$task AND GREATEST(dispatchtime,solvetime)>" . (time() - $CONFIG->getVal('chunktimeout')) . ") achunks ON achunks.agent=assignments.agent WHERE tasks.id=$task GROUP BY tasks.id");
  /*$res = $DB->query("SELECT * FROM tasks WHERE tasks.id=$task");
  $taskEntry = $res->fetch();
  $res = $DB->query("SELECT hashlists.name AS hname, hashlists.format, hashlists.hashtype AS htype, hashtypes.description AS htypename FROM hashlists LEFT JOIN hashtypes ON hashlists.hashtype=hashtypes.id WHERE hashlists.id=".$taskEntry['hashlist']);
  $taskEntry = array_merge($taskEntry, $res->fetch());
  $res = $DB->query("SELECT task,SUM(progress) AS cprogress,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks WHERE task=".$taskEntry['id']." GROUP BY task");
  $taskEntry = array_merge($taskEntry, $res->fetch());
  $res = $DB->query("SELECT * FROM assignments LEFT JOIN (SELECT DISTINCT agent, 1 AS working FROM chunks WHERE task=".$taskEntry['id']." AND GREATEST(dispatchtime,solvetime)>".(time()-$CONFIG->getVal('chunktimeout')).") achunks ON achunks.agent=assignments.agent WHERE assignments.task=".$taskEntry['id']." GROUP BY assignments.agent");
  $taskEntry = array_merge($taskEntry, $res->fetch());
  
  $taskEntry = $res->fetch();
  if ($taskEntry) {
    $taskSet->setValues($taskEntry);
    $taskSet->addValue("filter", $filter);
    $res = $DB->query("SELECT dispatchtime,solvetime FROM chunks WHERE task={$taskEntry['id']} AND solvetime>dispatchtime ORDER BY dispatchtime ASC");
    $intervaly = array();
    foreach ($res as $entry) {
      $interval = array();
      $interval["start"] = $entry["dispatchtime"];
      $interval["stop"] = $entry["solvetime"];
      $intervaly[] = $interval;
    }
    $soucet = 0;
    for ($i = 1; $i <= count($intervaly); $i++) {
      if (isset($intervaly[$i]) && $intervaly[$i]["start"] <= $intervaly[$i - 1]["stop"]) {
        $intervaly[$i]["start"] = $intervaly[$i - 1]["start"];
        if ($intervaly[$i]["stop"] < $intervaly[$i - 1]["stop"]) {
          $intervaly[$i]["stop"] = $intervaly[$i - 1]["stop"];
        }
      }
      else {
        $soucet += ($intervaly[$i - 1]["stop"] - $intervaly[$i - 1]["start"]);
      }
    }
    $taskSet->addValue("soucet", $soucet);
    
    $res = $DB->query("SELECT ROUND((tasks.keyspace-SUM(tchunks.length))/SUM(tchunks.length*tchunks.active/tchunks.time)) AS eta FROM (SELECT SUM(chunks.length*chunks.rprogress/10000) AS length,SUM(chunks.solvetime-chunks.dispatchtime) AS time,IF(MAX(solvetime)>=" . time() . "-" . $CONFIG->getVal('chunktimeout') . ",1,0) AS active FROM chunks WHERE chunks.solvetime>chunks.dispatchtime AND chunks.task={$taskEntry['id']} GROUP BY chunks.agent) tchunks CROSS JOIN tasks WHERE tasks.id={$taskEntry['id']}");
    $entry = $res->fetch();
    $taskSet->addValue('eta', $entry['eta']);
    
    $res = $DB->query("SELECT files.id,files.filename,files.size,files.secret FROM taskfiles JOIN files ON files.id=taskfiles.file WHERE task={$taskEntry['id']} ORDER BY filename");
    $res = $res->fetchAll();
    $attachFiles = array();
    foreach ($res as $file) {
      $set = new DataSet();
      $set->setValues($file);
      $attachFiles[] = $set;
    }
    $OBJECTS['attachFiles'] = $attachFiles;
    
    $agents = array();
    $res = $DB->query("SELECT agents.id,agents.active,agents.trusted,agents.name,assignments.benchmark,assignments.autoadjust,IF(chunks.lastact>=" . (time() - $CONFIG->getVal('chunktimeout')) . ",1,0) AS working,assignments.speed,IFNULL(chunks.lastact,0) AS time,IFNULL(chunks.searched,0) AS searched,chunks.spent,IFNULL(chunks.cracked,0) AS cracked FROM agents JOIN assignments ON agents.id=assignments.agent JOIN tasks ON tasks.id=assignments.task LEFT JOIN (SELECT agent,SUM(progress) AS searched,SUM(solvetime-dispatchtime) AS spent,SUM(cracked) AS cracked,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks WHERE task=$task AND solvetime>dispatchtime GROUP BY agent) chunks ON chunks.agent=agents.id WHERE assignments.task=$task GROUP BY agents.id ORDER BY agents.id");
    $res = $res->fetchAll();
    foreach ($res as $agent) {
      $set = new DataSet();
      $set->setValues($agent);
      $agents[] = $set;
    }
    $OBJECTS['agents'] = $agents;
    
    $allAgents = array();
    $res = $DB->query("SELECT agents.id,agents.active,agents.trusted,agents.name,IF(chunks.lastact>=" . (time() - $CONFIG->getVal('chunktimeout')) . ",1,0) AS working,IFNULL(chunks.lastact,0) AS time,IFNULL(chunks.searched,0) AS searched,chunks.spent,IFNULL(chunks.cracked,0) AS cracked FROM agents LEFT JOIN (SELECT agent,SUM(progress) AS searched,SUM(solvetime-dispatchtime) AS spent,SUM(cracked) AS cracked,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks WHERE task=$task AND solvetime>dispatchtime GROUP BY agent) chunks ON chunks.agent=agents.id WHERE spent IS NOT NULL GROUP BY agents.id ORDER BY agents.id");
    $res = $res->fetchAll();
    foreach ($res as $agent) {
      $set = new DataSet();
      $set->setValues($agent);
      $allAgents[] = $set;
    }
    $OBJECTS['allAgents'] = $allAgents;
    $showAll = false;
    if (isset($_GET['allagents'])) {
      $showAll = true;
    }
    $OBJECTS['showAllAgents'] = $showAll;
    
    $assignAgents = array();
    $res = $DB->query("SELECT agents.id,agents.name FROM agents LEFT JOIN assignments ON assignments.agent=agents.id WHERE IFNULL(assignments.task,0)!=$task ORDER BY agents.id ASC");
    $res = $res->fetchAll();
    foreach ($res as $agent) {
      $set = new DataSet();
      $set->setValues($agent);
      $assignAgents[] = $set;
    }
    $OBJECTS['assignAgents'] = $assignAgents;
    
    $add = "";
    if ($filter != '1') {
      $add = "AND progress<length ";
    }
    $chunks = array();
    $res = $DB->query("SELECT chunks.*,GREATEST(chunks.dispatchtime,chunks.solvetime)-chunks.dispatchtime AS spent,agents.name FROM chunks LEFT JOIN agents ON chunks.agent=agents.id WHERE task=$task " . $add . "ORDER BY chunks.dispatchtime DESC LIMIT 100");
    $res = $res->fetchAll();
    foreach ($res as $chunk) {
      $set = new DataSet();
      $set->setValues($chunk);
      $active = (max($chunk['dispatchtime'], $chunk['solvetime']) > time() - $CONFIG->getVal('chunktimeout') && $chunk['progress'] < $chunk['length'] && $chunk["state"] < 4);
      $set->addValue('active', $active);
      $chunks[] = $set;
    }
    $OBJECTS['chunks'] = $chunks;
    $OBJECTS['task'] = $taskSet;
  }*/
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
  if (isset($_GET["copy"])) {
    //copied from a task
    $copy = $FACTORIES::getTaskFactory()->get($_GET['copy']);
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
      $origFiles[] = $f->getId();
    }
  }
  $oF = new OrderFilter("filename", "ASC");
  $allFiles = $FACTORIES::getFileFactory()->filter(array('order' => $oF));
  $files = array();
  foreach($allFiles as $singleFile){
    $set = new DataSet();
    $set->addValue('checked', (in_array($singleFile->getId(), $origFiles))?"1":"0");
    $set->addValue('file', $singleFile);
    $files[] = $set;
  }
  $OBJECTS['files'] = $files;
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
    $OBJECTS['tasks'] = $tasks;
  }
}

echo $TEMPLATE->render($OBJECTS);




