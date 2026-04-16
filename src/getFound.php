<?php

use DBA\Agent;
use DBA\ContainFilter;
use DBA\Hash;
use DBA\Hashlist;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::GETHASHLIST_VIEW_PERM);

// TODO: configure max memory usage here
ini_set("max_execution_time", 100000);

$token = @$_GET['token'];
$qF = new QueryFilter(Agent::TOKEN, $token, "=");
$agent = Factory::getAgentFactory()->filter([Factory::FILTER => $qF], true);
if (!$agent) {
  die("No access!");
}
else if (!isset($_GET['hashlists'])) {
  die("No hashlists set!");
}
$qF = new ContainFilter(Hashlist::HASHLIST_ID, explode(",", $_GET['hashlists']));
$hashlists = Factory::getHashlistFactory()->filter([Factory::FILTER => $qF]);
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
switch ($format) {
  case DHashlistFormat::PLAIN:
    header_remove("Content-Type");
    header('Content-Type: text/plain');
    foreach ($hashlists as $hashlist) {
      $limit = 0;
      $size = SConfig::getInstance()->getVal(DConfig::BATCH_SIZE);
      do {
        $oF = new OrderFilter(Hash::HASH_ID, "ASC LIMIT $limit,$size");
        $qF1 = new QueryFilter(Hash::HASHLIST_ID, $hashlist->getId(), "=");
        $qF2 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
        $current = Factory::getHashFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]);
        
        $output = "";
        foreach ($current as $entry) {
          $output .= $entry->getHash();
          if (strlen($entry->getSalt()) > 0) {
            $salts = explode($hashlist->getSaltSeparator(), $entry->getSalt()); // Double salt
            foreach ($salts as $salt) {
              $output .= "\t" . $salt;
            }
          }
          $output .= "\tFF" . $lineDelimiter;
        }
        echo $output;
        
        $limit += $size;
      } while (sizeof($current) > 0);
    }
    break;
}