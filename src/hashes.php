<?php

use DBA\Chunk;
use DBA\ContainFilter;
use DBA\Hash;
use DBA\HashBinary;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::HASHES_VIEW_PERM);

Template::loadInstance("hashes/index");
Menu::get()->setActive("hashes");

// show hashes based on provided criteria
$hlist = 0;
$chunk = 0;
$task = 0;
$src = "";
$srcId = 0;
$binaryFormat = false;
$isWpa = false;
$hashFactory = null;
$hashClass = null;
$queryFilters = array();
UI::add('pageTitle', " Hashes");
if (isset($_GET['hashlist'])) {
  $list = Factory::getHashlistFactory()->get($_GET["hashlist"]);
  if ($list == null) {
    UI::printError("ERROR", "Invalid hashlist!");
  }
  UI::add('list', $list);
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
    $hashFactory = Factory::getHashFactory();
    $hashClass = \DBA\Hash::class;
  }
  else {
    $hashFactory = Factory::getHashBinaryFactory();
    $binaryFormat = true;
    if ($hashlist->getFormat() == DHashlistFormat::WPA) {
      $isWpa = true;
    }
    $hashClass = \DBA\HashBinary::class;
  }
  $src = "hashlist";
  $srcId = $list->getId();
  UI::add('pageTitle', "Hashes of Hashlist " . $list->getHashlistName());
}
else if (isset($_GET['chunk'])) {
  $jF1 = new JoinFilter(Factory::getTaskFactory(), Task::TASK_ID, Chunk::TASK_ID, Factory::getChunkFactory());
  $qF = new QueryFilter(Chunk::CHUNK_ID, $_GET['chunk'], "=", Factory::getChunkFactory());
  $joined = Factory::getChunkFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF1]);
  /** @var $chunks Chunk[] */
  $chunks = $joined[Factory::getChunkFactory()->getModelName()];
  if (sizeof($chunks) == null) {
    UI::printError("ERROR", "Invalid chunk!");
  }
  $chunk = $chunks[0];
  $hashlists = Util::checkSuperHashlist(Factory::getHashlistFactory()->get(Factory::getTaskWrapperFactory()->get(Factory::getTaskFactory()->get($chunk->getTaskId())->getTaskWrapperId())->getHashlistId()));
  if ($hashlists[0]->getFormat() == DHashlistFormat::PLAIN) {
    $hashFactory = Factory::getHashFactory();
    $hashClass = \DBA\Hash::class;
  }
  else {
    $hashFactory = Factory::getHashBinaryFactory();
    $hashClass = \DBA\HashBinary::class;
    if ($hashlists[0]->getFormat() == DHashlistFormat::WPA) {
      $isWpa = true;
    }
    $binaryFormat = true;
  }
  $queryFilters[] = new QueryFilter(Hash::CHUNK_ID, $chunk->getId(), "=");
  $src = "chunk";
  UI::add('chunk', $chunk);
  $srcId = $chunk->getId();
  UI::add('pageTitle', "Hashes of Chunk " . $chunk->getId());
}
else if (isset($_GET['task'])) {
  $task = Factory::getTaskFactory()->get($_GET['task']);
  if ($task == null) {
    UI::printError("ERROR", "Invalid task!");
  }
  $hashlists = Util::checkSuperHashlist(Factory::getHashlistFactory()->get(Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId())->getHashlistId()));
  if ($hashlists[0]->getFormat() == DHashlistFormat::PLAIN) {
    $hashFactory = Factory::getHashFactory();
    $hashClass = \DBA\Hash::class;
  }
  else {
    $hashFactory = Factory::getHashBinaryFactory();
    $binaryFormat = true;
    if ($hashlists[0]->getFormat() == DHashlistFormat::WPA) {
      $isWpa = true;
    }
    $hashClass = \DBA\HashBinary::class;
  }
  $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
  $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
  $chunkIds = array();
  foreach ($chunks as $chunk) {
    $chunkIds[] = $chunk->getId();
  }
  $queryFilters[] = new ContainFilter(Hash::CHUNK_ID, $chunkIds);
  $src = "task";
  UI::add('task', $task);
  $srcId = $task->getId();
  UI::add('pageTitle', "Hashes of Task " . $task->getId());
}

UI::add('src', $src);
UI::add('srcId', $srcId);

$displaying = "";
if (isset($_GET['display'])) {
  $displaying = $_GET["display"];
}
UI::add('displaying', htmlentities($displaying, ENT_QUOTES, "UTF-8"));
$filter = "";
if (isset($_GET['filter'])) {
  $filter = $_GET['filter'];
}
UI::add('filtering', htmlentities($filter, ENT_QUOTES, "UTF-8"));

$displays = array("hash" => "Hashes only", "" => "Hashes + plaintexts", "plain" => "Plaintexts only");
$filters = array("cracked" => "Cracked", "uncracked" => "Uncracked", "" => "All");

$displaysSet = array();
foreach ($displays as $id => $text) {
  $set = new DataSet();
  $set->addValue('id', $id);
  $set->addValue('text', $text);
  $displaysSet[] = $set;
}
UI::add('displays', $displaysSet);

$filtersSet = array();
foreach ($filters as $id => $text) {
  $set = new DataSet();
  $set->addValue('id', $id);
  $set->addValue('text', $text);
  $filtersSet[] = $set;
}
UI::add('filters', $filtersSet);

if ($filter == "cracked") {
  $queryFilters[] = new QueryFilter(Hash::IS_CRACKED, 1, "=");
}
else if ($filter == "uncracked") {
  $queryFilters[] = new QueryFilter(Hash::IS_CRACKED, 0, "=");
}

$count = $hashFactory->countFilter([Factory::FILTER => $queryFilters]);
$numPages = $count / SConfig::getInstance()->getVal(DConfig::HASHES_PER_PAGE);
if ($numPages * SConfig::getInstance()->getVal(DConfig::HASHES_PER_PAGE) != $count) {
  $numPages++;
}
UI::add('count', $count);

$currentPage = 0;
$nextPage = -1;
$previousPage = -1;
if (isset($_GET['page']) && $_GET['page'] >= 0 && $_GET['page'] < $numPages) {
  $currentPage = intval($_GET['page']);
}
if ($currentPage > 0) {
  $previousPage = $currentPage - 1;
}
if ($currentPage < $numPages - 1) {
  $nextPage = $currentPage + 1;
}
UI::add('numPages', $numPages);
UI::add('nextPage', $nextPage);
UI::add('previousPage', $previousPage);
UI::add('currentPage', $currentPage);

$oF = new OrderFilter($hashFactory->getNullObject()->getPrimaryKey(), "ASC LIMIT " . (SConfig::getInstance()->getVal(DConfig::HASHES_PER_PAGE)) . " OFFSET " . (SConfig::getInstance()->getVal(DConfig::HASHES_PER_PAGE) * $currentPage));
$hashes = $hashFactory->filter([Factory::FILTER => $queryFilters, Factory::ORDER => $oF]);

if (isset($_GET['crackpos']) && $_GET['crackpos'] == 'true') {
  UI::add('crackpos', 1);
}
else {
  UI::add('crackpos', 0);
}

$output = "";
foreach ($hashes as $hash) {
  $hash = \DBA\Util::cast($hash, $hashClass);
  if ($displaying == "") {
    if (!$binaryFormat) {
      $output .= htmlentities($hash->getHash(), ENT_QUOTES, "UTF-8");
    }
    else if ($binaryFormat && $isWpa) {
      /** @var $hash HashBinary */
      $output .= htmlentities($hash->getEssid(), ENT_QUOTES, "UTF-8");
    }
    else {
      $output .= "[...]";
    }
    if (!$binaryFormat && strlen($hash->getSalt()) > 0) {
      $output .= ":" . htmlentities($hash->getSalt(), ENT_QUOTES, "UTF-8");
    }
    if ($filter == "cracked" || $filter == "") {
      if ($hash->getIsCracked() == 1) {
        $output .= ":" . htmlentities($hash->getPlaintext(), ENT_QUOTES, "UTF-8");
      }
    }
  }
  else if ($displaying == "hash") {
    $output .= $hash->getHash();
    if (!$binaryFormat && strlen($hash->getSalt()) > 0) {
      $output .= ":" . htmlentities($hash->getSalt(), ENT_QUOTES, "UTF-8");
    }
  }
  else if ($displaying == "plain") {
    if ($hash->getIsCracked() == 1) {
      $output .= htmlentities($hash->getPlaintext(), ENT_QUOTES, "UTF-8");
    }
    else {
      continue;
    }
  }
  if (isset($_GET['crackpos']) && $_GET['crackpos'] == 'true' && $hash->getIsCracked()) {
    $output .= ":" . $hash->getCrackPos();
  }
  $output .= "\n";
}
UI::add('output', $output);

echo Template::getInstance()->render(UI::getObjects());




