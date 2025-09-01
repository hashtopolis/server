<?php

use DBA\LikeFilterInsensitive;
use DBA\StoredValue;
use DBA\Factory;
use Composer\Semver\Comparator;

/*
 * This script should automatically determine the current base function and go through
 * the newer update scripts and run all actions which need to be executed.
 */

if (!isset($TEST)) {
  require_once(dirname(__FILE__) . "/../../inc/confv2.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/info.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
}

$qF = new LikeFilterInsensitive(StoredValue::STORED_VALUE_ID, "update_%");
$entries = Factory::getStoredValueFactory()->filter([Factory::FILTER => $qF]);
$PRESENT = [];
foreach ($entries as $entry) {
  $PRESENT[substr($entry->getId(), 7)] = true;
}

$EXECUTED = [];

// determine which update scripts it needs to consider
$storedVersion = Factory::getStoredValueFactory()->get("version");
$storedBuild = Factory::getStoredValueFactory()->get("build");
$upgradePossible = true;
if ($storedVersion == null) {
  // we just save the current version and assume that the upgrade was executed up to this version
  $storedVersion = new StoredValue("version", explode("+", $VERSION)[0]);
  Factory::getStoredValueFactory()->save($storedVersion);
  $upgradePossible = false;
}
if ($storedBuild == null) {
  // we just save the current build and assume that the upgrade was executed up to this build
  $storedBuild = new StoredValue("build", ($BUILD == 'repository') ? Util::getGitCommit(true) : $BUILD);
  Factory::getStoredValueFactory()->save($storedBuild);
  $upgradePossible = false;
}
if ($upgradePossible) { // we can actually check if there are upgrades to be applied
  $allFiles = scandir(dirname(__FILE__));
  usort($allFiles, array("Util", "updateVersionComparison"));
  foreach ($allFiles as $file) {
    if (Util::startsWith($file, "update_v")) {
      $startVersion = substr($file, 8, strpos($file, "_", 7) - 8);
      if (Comparator::greaterThanOrEqualTo($startVersion, $storedVersion->getVal())) {
        // script needs to be executed
        include(dirname(__FILE__) . "/" . $file);
      }
    }
  }
  
  $stores = [];
  foreach ($EXECUTED as $key => $val) {
    $stores[] = new StoredValue("update_" . $key, "1");
  }
  
  if (sizeof($stores) > 0) {
    Factory::getStoredValueFactory()->massSave($stores);
  }
  
  // save the new version
  $storedVersion->setVal(explode("+", $VERSION)[0]);
  Factory::getStoredValueFactory()->update($storedVersion);
  $storedBuild->setVal(($BUILD == 'repository') ? Util::getGitCommit(true) : $BUILD);
  Factory::getStoredValueFactory()->update($storedBuild);
}
