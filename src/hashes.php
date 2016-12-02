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

$TEMPLATE = new Template("hashes/index");
$MENU->setActive("hashes");

// show hashes based on provided criteria
$hlist = 0;
$chunk = 0;
$task = 0;
$src = "";
$srcId = 0;
$hashFactory = null;
$queryFilters = array();
if (isset($_GET['hashlist'])) {
  $list = $FACTORIES::getHashlistFactory()->get($_GET["hashlist"]);
  if($list == null){
    UI::printError("ERROR", "Invalid hashlist!");
  }
  $OBJECTS['list'] = $list;
  if($list->getFormat() == 3){
    $lists = Util::checkSuperHashlist($list);
    $listIds = array();
    foreach($lists as $l){
      $listIds[] = $l;
    }
    $queryFilters[] = new ContainFilter("hashlistId", $listIds);
    $hashlist = $lists[0];
  }
  else{
    $hashlist = $list;
    $queryFilters[] = new QueryFilter("hashlistId", $list->getId(), "=");
  }
  if($hashlist->getFormat() == 0){
    $hashFactory = $FACTORIES::getHashFactory();
  }
  else{
    $hashFactory = $FACTORIES::getHashBinaryFactory();
  }
  $src = "hashlist";
  $srcId = $list->getId();
}
else if (isset($_GET['chunk'])) {
  $jF1 = new JoinFilter($FACTORIES::getTaskFactory(), "taskId", "taskId", $FACTORIES::getChunkFactory());
  $jF2 = new JoinFilter($FACTORIES::getHashlistFactory(), "hashlistId", "hashlistId", $FACTORIES::getTaskFactory());
  $qF = new QueryFilter("chunkId", $_GET['chunk'], "=", $FACTORIES::getChunkFactory());
  $joined = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF, 'join' => array($jF1, $jF2)));
  if(sizeof($joined['Chunk']) == null){
    UI::printError("ERROR", "Invalid chunk!");
  }
  $chunk = $joined['Chunk'];
  $list = $joined['Hashlist'];
  if($list->getFormat() == 3){
    $lists = Util::checkSuperHashlist($list);
    $hashlist = $lists[0];
  }
  if($hashlist->getFormat() == 0){
    $hashFactory = $FACTORIES::getHashFactory();
  }
  else{
    $hashFactory = $FACTORIES::getHashBinaryFactory();
  }
  $queryFilters[] = new QueryFilter("chunkId", $chunk->getId(), "=");
  $src = "chunk";
  $srcId = $chunk->getId();
}
else if (isset($_GET['task'])) {
  $jF = new JoinFilter($FACTORIES::getHashlistFactory(), "hashlistId", "hashlistId");
  $qF = new QueryFilter("taskId", $_GET['task'], "=");
  $joined = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF, 'join' => array($jF)));
  if(sizeof($joined['Task']) == null){
    UI::printError("ERROR", "Invalid task!");
  }
  $task = $joined['Task'];
  $list = $joined['Hashlist'];
  if($list->getFormat() == 3){
    $lists = Util::checkSuperHashlist($list);
    $hashlist = $lists[0];
  }
  if($hashlist->getFormat() == 0){
    $hashFactory = $FACTORIES::getHashFactory();
  }
  else{
    $hashFactory = $FACTORIES::getHashBinaryFactory();
  }
  $qF = new QueryFilter("taskId", $task->getId(), "=");
  $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF));
  $chunkIds = array();
  foreach($chunks as $chunk){
    $chunkIds[] = $chunk->getId();
  }
  $queryFilters[] = new ContainFilter("chunkId", $chunkIds);
  $src = "task";
  $srcId = $task->getId();
}

$OBJECTS['src'] = $src;
$OBJECTS['srcId'] = $srcId;

$displaying = "";
if (isset($_GET['display'])) {
  $displaying = $_GET["display"];
}
$OBJECTS['displaying'] = $displaying;
$filter = "";
if (isset($_GET['filter'])) {
  $filter = $_GET['filter'];
}
$OBJECTS['filtering'] = $filter;

$displays = array("hash" => "Hashes only", "" => "Hashes + plaintexts", "plain" => "Plaintexts only");
$filters = array("cracked" => "Cracked", "uncracked" => "Uncracked", "" => "All");

$displaysSet = array();
foreach ($displays as $id => $text) {
  $set = new DataSet();
  $set->addValue('id', $id);
  $set->addValue('text', $text);
  $displaysSet[] = $set;
}
$OBJECTS['displays'] = $displaysSet;

$filtersSet = array();
foreach ($filters as $id => $text) {
  $set = new DataSet();
  $set->addValue('id', $id);
  $set->addValue('text', $text);
  $filtersSet[] = $set;
}
$OBJECTS['filters'] = $filtersSet;

if($filter == "cracked"){
  $filters[] = new QueryFilter("isCracked", "1", "=");
}
else if($filter == "uncracked"){
  $filters[] = new QueryFilter("isCracked", "0", "=");
}

$count = $hashFactory->countFilter(array('filter' => $queryFilters));
$numPages = $count/1000;
if($numPages*1000 != $count){
  $numPages++;
}
$OBJECTS['count'] = $count;

$currentPage = 0;
$nextPage = -1;
$previousPage = -1;
if(isset($_GET['page']) && $_GET['page'] >= 0 && $_GET['page'] < $numPages){
  $currentPage = $_GET['page'];
}
if($currentPage > 0){
  $previousPage = $currentPage - 1;
}
if($currentPage < $numPages - 1){
  $nextPage = $currentPage + 1;
}
$OBJECTS['numPages'] = $numPages;
$OBJECTS['nextPage'] = $nextPage;
$OBJECTS['previousPage'] = $previousPage;
$OBJECTS['currentPage'] = $currentPage;

$oF = new OrderFilter("hashId", "ASC LIMIT ".(1000*$currentPage).", 1000");
$hashes = $hashFactory->filter(array('filter' => $queryFilters, 'order' => $oF));

$output = "";
foreach($hashes as $hash){
  if($displaying == ""){
    $output .= $hash->getHash();
    if($filter == "cracked" || $filter == ""){
      $output .= ":" . htmlentities($hash->getPlaintext(), false, "UTF-8");
    }
  }
  else if($displaying == "hash"){
    $output .= $hash->getHash();
  }
  else if($displaying == "plain"){
    $output .= htmlentities($hash->getPlaintext(), false, "UTF-8");
  }
  $output .= "\n";
}
$OBJECTS['output'] = $output;




/*
$valid = false;
if ($chunk > 0) {
  $res = AbstractModelFactory::getDB()->query("SELECT hashlists.id,hashlists.format FROM chunks JOIN tasks ON chunks.task=tasks.id JOIN hashlists ON hashlists.id=tasks.hashlist WHERE chunks.id=$chunk");
  $chunkRes = $res->fetch();
  if (!$chunkRes) {
    $message = "<div class='alert alert-danger'>Invalid Chunk!</div>";
  }
  else {
    $hlist = $chunkRes['id'];
    $format = $chunkRes['format'];
    $valid = true;
  }
}
else if ($task > 0) {
  $res = AbstractModelFactory::getDB()->query("SELECT hashlists.id,tasks.name,hashlists.format FROM tasks JOIN hashlists ON hashlists.id=tasks.hashlist WHERE tasks.id=$task");
  $taskRes = $res->fetch();
  if (!$taskRes) {
    $message = "<div class='alert alert-danger'>Invalid task!</div>";
  }
  else {
    $hlist = $taskRes['id'];
    $format = $taskRes["format"];
    $valid = true;
  }
}
else if ($hlist > 0) {
  $res = AbstractModelFactory::getDB()->query("SELECT name,format FROM hashlists WHERE id=$hlist");
  $hlistRes = $res->fetch();
  if (!$hlistRes) {
    $message = "<div class='alert alert-danger'>Invalid hashlist!</div>";
  }
  else {
    $format = $hlistRes["format"];
    $valid = true;
  }
}

if ($valid) {
  $OBJECTS['src'] = $src;
  $OBJECTS['srcId'] = $srcId;
  
  // create proper superhashlist field if needed
  list($superhash, $hlisty) = Util::superList($hlist, $format);
  
  switch ($src) {
    case "chunk":
      $viewfilter = "WHERE chunk=$chunk";
      break;
    case "task":
      $viewfilter = "JOIN chunks ON chunk=chunks.id WHERE " . Util::getStaticArray($format, 'formattables') . ".chunk IS NOT NULL AND chunks.task=$task";
      break;
    case "hashlist":
      $viewfilter = "WHERE hashlist IN ($hlisty)";
      break;
  }
  $displaying = "";
  if (isset($_GET['display'])) {
    $displaying = $_GET["display"];
  }
  $OBJECTS['displaying'] = $displaying;
  $filter = "";
  if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
  }
  $OBJECTS['filtering'] = $filter;
  
  $displays = array("hash" => "Hashes only", "" => "Hashes + plaintexts", "plain" => "Plaintexts only");
  $filters = array("cracked" => "Cracked", "uncracked" => "Uncracked", "" => "All");
  
  $displaysSet = array();
  foreach ($displays as $id => $text) {
    $set = new DataSet();
    $set->addValue('id', $id);
    $set->addValue('text', $text);
    $displaysSet[] = $set;
  }
  $OBJECTS['displays'] = $displaysSet;
  
  $filtersSet = array();
  foreach ($filters as $id => $text) {
    $set = new DataSet();
    $set->addValue('id', $id);
    $set->addValue('text', $text);
    $filtersSet[] = $set;
  }
  $OBJECTS['filters'] = $filtersSet;
  
  $filter = array("cracked" => " AND plaintext IS NOT NULL", "uncracked" => " AND plaintext IS NULL", "" => "");
  $kve = "SELECT ";
  switch ($format) {
    case 0:
      // get regular hashes
      $kve .= "hashes.hash,hashes.salt,hashes.plaintext";
      break;
    
    case 1:
      // get access points and their passwords
      $kve .= "hashes_binary.essid AS hash,hashes_binary.plaintext";
      break;
    
    case 2:
      // get binary - only passwords
      $kve .= "'' AS hash,hashes_binary.plaintext";
      break;
  }
  $kve .= " FROM " . Util::getStaticArray($format, 'formattables') . " " . $viewfilter . $filter[$filter];
  
  $res = AbstractModelFactory::getDB()->query($kve);
  $res = $res->fetchAll();
  $output = "";
  foreach ($res as $entry) {
    $out = "";
    if (strlen($entry['hash']) == 0) {
      continue;
    }
    switch ($displaying) {
      case 'hash':
        $out .= $entry['hash'];
        if ($entry['salt'] != "") {
          $out .= $CONFIG->getVal('fieldseparator') . $entry['salt'];
        }
        break;
      case '':
        $out .= $entry['hash'];
        if (isset($entry['salt']) && $entry['salt'] != "") {
          $out .= $CONFIG->getVal('fieldseparator') . $entry['salt'];
        }
        $out .= $CONFIG->getVal('fieldseparator');
      case 'plain':
        if ($entry['plaintext'] != "") {
          $out .= str_replace(" ", "[space]", $entry['plaintext']);
        }
        break;
    }
    if (strlen($out) > 0) {
      //$output .= htmlentities($out, false, "UTF-8")."\n";
      $output .= $out . "\n";
    }
  }
  $OBJECTS['matches'] = $output;
  $OBJECTS['numMatches'] = sizeof($res);
}

$OBJECTS['message'] = $message;*/

echo $TEMPLATE->render($OBJECTS);




