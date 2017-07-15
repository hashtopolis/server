<?php

use DBA\Chunk;
use DBA\ContainFilter;
use DBA\Hash;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::READ_ONLY) {
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
$binaryFormat = false;
$hashFactory = null;
$hashClass = null;
$queryFilters = array();
if (isset($_GET['hashlist'])) {
  $list = $FACTORIES::getHashlistFactory()->get($_GET["hashlist"]);
  if ($list == null) {
    UI::printError("ERROR", "Invalid hashlist!");
  }
  $OBJECTS['list'] = $list;
  if ($list->getFormat() == DHashlistFormat::SUPERHASHLIST) {
    $lists = Util::checkSuperHashlist($list);
    $listIds = array();
    foreach ($lists as $l) {
      $listIds[] = $l->getId();
    }
    $queryFilters[] = new ContainFilter(Hash::HASHLIST_ID, $listIds);
    $hashlist = $lists[0];
  }
  else {
    $hashlist = $list;
    $queryFilters[] = new QueryFilter(Hash::HASHLIST_ID, $list->getId(), "=");
  }
  if ($hashlist->getFormat() == DHashlistFormat::PLAIN) {
    $hashFactory = $FACTORIES::getHashFactory();
    $hashClass = \DBA\Hash::class;
  }
  else {
    $hashFactory = $FACTORIES::getHashBinaryFactory();
    $binaryFormat = true;
    $hashClass = \DBA\HashBinary::class;
  }
  $src = "hashlist";
  $srcId = $list->getId();
}
else if (isset($_GET['chunk'])) {
  $jF1 = new JoinFilter($FACTORIES::getTaskFactory(), Task::TASK_ID, Chunk::TASK_ID, $FACTORIES::getChunkFactory());
  $jF2 = new JoinFilter($FACTORIES::getHashlistFactory(), Hashlist::HASHLIST_ID, Task::HASHLIST_ID, $FACTORIES::getTaskFactory());
  $qF = new QueryFilter(Chunk::CHUNK_ID, $_GET['chunk'], "=", $FACTORIES::getChunkFactory());
  $joined = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => array($jF1, $jF2)));
  if (sizeof($joined[$FACTORIES::getChunkFactory()->getModelName()]) == null) {
    UI::printError("ERROR", "Invalid chunk!");
  }
  $chunk = \DBA\Util::cast($joined[$FACTORIES::getChunkFactory()->getModelName()][0], \DBA\Chunk::class);
  $list = \DBA\Util::cast($joined[$FACTORIES::getHashlistFactory()->getModelName()][0], \DBA\Hashlist::class);
  $hashlist = $list;
  if ($list->getFormat() == DHashlistFormat::SUPERHASHLIST) {
    $lists = Util::checkSuperHashlist($list);
    $hashlist = $lists[0];
  }
  if ($hashlist->getFormat() == DHashlistFormat::PLAIN) {
    $hashFactory = $FACTORIES::getHashFactory();
    $hashClass = \DBA\Hash::class;
  }
  else {
    $hashFactory = $FACTORIES::getHashBinaryFactory();
    $hashClass = \DBA\HashBinary::class;
    $binaryFormat = true;
  }
  $queryFilters[] = new QueryFilter(Hash::CHUNK_ID, $chunk->getId(), "=");
  $src = "chunk";
  $OBJECTS['chunk'] = $chunk;
  $srcId = $chunk->getId();
}
else if (isset($_GET['task'])) {
  $jF = new JoinFilter($FACTORIES::getHashlistFactory(), Hashlist::HASHLIST_ID, Task::HASHLIST_ID);
  $qF = new QueryFilter(Task::TASK_ID, $_GET['task'], "=");
  $joined = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => array($jF)));
  if (sizeof($joined[$FACTORIES::getTaskFactory()->getModelName()]) == null) {
    UI::printError("ERROR", "Invalid task!");
  }
  $task = Util::cast($joined[$FACTORIES::getTaskFactory()->getModelName()][0], \DBA\Task::class);
  $hashlist = Util::cast($joined[$FACTORIES::getHashlistFactory()->getModelName()][0], \DBA\Hashlist::class);
  if ($hashlist->getFormat() == DHashlistFormat::SUPERHASHLIST) {
    $lists = Util::checkSuperHashlist($hashlist);
    $hashlist = $lists[0];
  }
  if ($hashlist->getFormat() == DHashlistFormat::PLAIN) {
    $hashFactory = $FACTORIES::getHashFactory();
    $hashClass = \DBA\Hash::class;
  }
  else {
    $hashFactory = $FACTORIES::getHashBinaryFactory();
    $binaryFormat = true;
    $hashClass = \DBA\HashBinary::class;
  }
  $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
  $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
  $chunkIds = array();
  foreach ($chunks as $chunk) {
    $chunkIds[] = $chunk->getId();
  }
  $queryFilters[] = new ContainFilter(Hash::CHUNK_ID, $chunkIds);
  $src = "task";
  $OBJECTS['task'] = $task;
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

if ($filter == "cracked") {
  $queryFilters[] = new QueryFilter(Hash::IS_CRACKED, "1", "=");
}
else if ($filter == "uncracked") {
  $queryFilters[] = new QueryFilter(Hash::IS_CRACKED, "0", "=");
}

$count = $hashFactory->countFilter(array($FACTORIES::FILTER => $queryFilters));
$numPages = $count / 1000;
if ($numPages * 1000 != $count) {
  $numPages++;
}
$OBJECTS['count'] = $count;

$currentPage = 0;
$nextPage = -1;
$previousPage = -1;
if (isset($_GET['page']) && $_GET['page'] >= 0 && $_GET['page'] < $numPages) {
  $currentPage = $_GET['page'];
}
if ($currentPage > 0) {
  $previousPage = $currentPage - 1;
}
if ($currentPage < $numPages - 1) {
  $nextPage = $currentPage + 1;
}
$OBJECTS['numPages'] = $numPages;
$OBJECTS['nextPage'] = $nextPage;
$OBJECTS['previousPage'] = $previousPage;
$OBJECTS['currentPage'] = $currentPage;

$oF = new OrderFilter($hashFactory->getNullObject()->getPrimaryKey(), "ASC LIMIT " . (1000 * $currentPage) . ", 1000");
$hashes = $hashFactory->filter(array($FACTORIES::FILTER => $queryFilters, $FACTORIES::ORDER => $oF));

$output = "";
foreach ($hashes as $hash) {
  $hash = \DBA\Util::cast($hash, $hashClass);
  if ($displaying == "") {
    if (!$binaryFormat) {
      $output .= $hash->getHash();
    }
    else {
      $output .= "[...]";
    }
    if (!$binaryFormat && strlen($hash->getSalt()) > 0) {
      $output .= ":" . htmlentities($hash->getSalt(), false, "UTF-8");
    }
    if ($filter == "cracked" || $filter == "") {
      if ($hash->getIsCracked() == 1) {
        $output .= ":" . htmlentities($hash->getPlaintext(), false, "UTF-8");
      }
    }
  }
  else if ($displaying == "hash") {
    $output .= $hash->getHash();
    if (!$binaryFormat && strlen($hash->getSalt()) > 0) {
      $output .= ":" . htmlentities($hash->getSalt(), false, "UTF-8");
    }
  }
  else if ($displaying == "plain") {
    if ($hash->getIsCracked() == 1) {
      $output .= htmlentities($hash->getPlaintext(), false, "UTF-8");
    }
    else {
      continue;
    }
  }
  $output .= "\n";
}
$OBJECTS['output'] = $output;

echo $TEMPLATE->render($OBJECTS);




