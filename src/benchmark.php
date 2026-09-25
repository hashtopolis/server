<?php

use Hashtopolis\dba\models\Benchmark;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\CSRF;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\Menu;
use Hashtopolis\inc\templating\Template;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessControl;
use Hashtopolis\inc\utils\BenchmarkUtils;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

// The benchmark cache is a server setting, so it lives behind the same
// permission as the rest of the server configuration.
AccessControl::getInstance()->checkPermission(DViewControl::CONFIG_VIEW_PERM);

Template::loadInstance("benchmark");
Menu::get()->setActive("config_benchmark");

if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  switch ($_POST['action']) {
    case 'invalidate':
      $benchmark = Factory::getBenchmarkFactory()->get(intval(@$_POST['benchmarkId']));
      if ($benchmark == null) {
        UI::addMessage(UI::ERROR, "Benchmark cache entry not found!");
      }
      else {
        Factory::getBenchmarkFactory()->delete($benchmark);
      }
      break;
    case 'prune':
      BenchmarkUtils::prune();
      break;
  }
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

// Resolve cracker binary ids to a readable name once, so the row loop does not
// query per entry.
$crackerNames = new DataSet();
foreach (Factory::getCrackerBinaryFactory()->filter([]) as $crackerBinary) {
  $crackerNames->addValue($crackerBinary->getId(), $crackerBinary->getBinaryName() . " " . $crackerBinary->getVersion());
}

$oF = new OrderFilter(Benchmark::CREATE_TIME, "DESC");
$entries = Factory::getBenchmarkFactory()->filter([Factory::ORDER => $oF]);

$now = time();
$rows = array();
$numExpired = 0;
foreach ($entries as $entry) {
  $expired = ($entry->getExpireTime() <= $now) ? 1 : 0;
  $numExpired += $expired;
  $crackerName = $crackerNames->getVal($entry->getCrackerBinaryId());

  $set = new DataSet();
  $set->addValue('id', $entry->getId());
  // Fall back to the raw id when the cracker binary was removed after the entry
  // was cached, so the row still identifies its key.
  $set->addValue('cracker', ($crackerName === null) ? "#" . $entry->getCrackerBinaryId() : $crackerName);
  $set->addValue('hashMode', $entry->getHashMode());
  // attackParameters and deviceSignature are SHA-256 hashes, so only a prefix
  // is worth showing.
  $set->addValue('attackShort', substr((string)$entry->getAttackParameters(), 0, 12));
  $set->addValue('deviceShort', substr((string)$entry->getDeviceSignature(), 0, 12));
  $set->addValue('benchmarkType', $entry->getBenchmarkType());
  $set->addValue('benchmarkValue', $entry->getBenchmarkValue());
  $set->addValue('created', $entry->getCreateTime());
  $set->addValue('expires', $entry->getExpireTime());
  $set->addValue('expired', $expired);
  $rows[] = $set;
}

UI::add('pageTitle', "Benchmark Cache");
UI::add('benchmarks', $rows);
UI::add('numEntries', count($rows));
UI::add('numExpired', $numExpired);
UI::add('cacheTtl', BenchmarkUtils::getTtl());

echo Template::getInstance()->render(UI::getObjects());
