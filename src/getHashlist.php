<?php

use DBA\Agent;
use DBA\ContainFilter;
use DBA\Hash;
use DBA\HashBinary;
use DBA\Hashlist;
use DBA\OrderFilter;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var DataSet $CONFIG */
/** @var array $OBJECTS */

$ACCESS_CONTROL->checkViewPermission(DViewControl::GETHASHLIST_VIEW_PERM);

// TODO: configure max memory usage here
ini_set("max_execution_time", 100000);

$token = @$_GET['token'];
$qF = new QueryFilter(Agent::TOKEN, $token, "=");
$agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF), true);
if (!$agent) {
  die("No access!");
}
else if (!isset($_GET['hashlists'])) {
  die("No hashlists set!");
}
$qF = new ContainFilter(Hashlist::HASHLIST_ID, explode(",", $_GET['hashlists']));
$hashlists = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => $qF));
if (sizeof($hashlists) == 0) {
  die("No hashlists found!");
}
foreach ($hashlists as $hashlist) {
  if ($agent->getIsTrusted() < $hashlist->getIsSecret()) {
    die("No access!");
  }
}

$lineDelimiter = "\n";
if ($agent->getOs() == DOperatingSystem::WINDOWS) {
  $lineDelimiter = "\r\n";
}

$format = $hashlists[0]->getFormat();
$count = 0;
switch ($format) {
  case DHashlistFormat::PLAIN:
    header_remove("Content-Type");
    header('Content-Type: text/plain');
    foreach ($hashlists as $hashlist) {
      $limit = 0;
      $size = $CONFIG->getVal(DConfig::BATCH_SIZE);
      do {
        $oF = new OrderFilter(Hash::HASH_ID, "ASC LIMIT $limit,$size");
        $qF1 = new QueryFilter(Hash::HASHLIST_ID, $hashlist->getId(), "=");
        $qF2 = new QueryFilter(Hash::IS_CRACKED, 0, "=");
        $current = $FACTORIES::getHashFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => array($oF)));
        
        $output = "";
        $count += sizeof($current);
        foreach ($current as $entry) {
          $output .= $entry->getHash();
          if (strlen($entry->getSalt()) > 0) {
            $output .= $hashlist->getSaltSeparator() . $entry->getSalt();
          }
          $output .= $lineDelimiter;
        }
        echo $output;
        
        $limit += $size;
      } while (sizeof($current) > 0);
    }
    break;
  case DHashlistFormat::BINARY:
  case DHashlistFormat::WPA:
    header_remove("Content-Type");
    header('Content-Type: application/octet-stream');
    $output = "";
    foreach ($hashlists as $hashlist) {
      $qF1 = new QueryFilter(HashBinary::HASHLIST_ID, $hashlist->getId(), "=");
      $qF2 = new QueryFilter(HashBinary::IS_CRACKED, 0, "=");
      $current = $FACTORIES::getHashBinaryFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
      $count += sizeof($current);
      foreach ($current as $entry) {
        $output .= Util::hextobin($entry->getHash());
      }
    }
    echo $output;
    break;
}

if ($count == 0) {
  die("No hashes are available to crack!");
}