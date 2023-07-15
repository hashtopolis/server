<?php
use DBA\QueryFilter;
use DBA\Factory;
use DBA\OrderFilter;
use DBA\CrackerBinary;

require_once(dirname(__FILE__) . "/inc/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

Template::loadInstance("benchmarks/index");

AccessControl::getInstance()->checkPermission(DViewControl::AGENTS_VIEW_PERM);

if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $benchmarkHandler = new BenchmarkHandler();
  $benchmarkHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['id'])) {
  //go to detail page
  // Template::loadInstance("benchmark/detail");
  $agent = Factory::getAgentFactory()->get($_GET['id']);
  if (!$agent) {
    UI::printError("ERROR", "Agent not found!");
  } else {
  $qF = new QueryFilter("agentId", $agent->getId(), "="); 
  $benchmarks = Factory::getBenchmarkFactory()->filter([Factory::FILTER => [$qF]]);
  }
} else {
  $benchmarks = Factory::getBenchmarkFactory()->filter([]);
}

$oF = new OrderFilter(CrackerBinary::CRACKER_BINARY_ID, "DESC");
$versions = Factory::getCrackerBinaryFactory()->filter([Factory::ORDER => $oF]);
usort($versions, ["Util", "versionComparisonBinary"]);
 
 foreach ($benchmarks as $benchmark) {
   //format the devices pretty
   $devices = HardwareGroupUtils::getDevicesFromBenchmark($benchmark);

   $tmp_devices_tuple = array_count_values(explode("\n", $devices));
   $devices_tuple = array();
   foreach ($tmp_devices_tuple as $key => $value) {
     $devices_tuple[] = str_replace("*", "&nbsp;&nbsp", sprintf("%'*2d&times ", $value) . $key);
    }
    $benchmark->setHardwareGroupId(implode("\n", $devices_tuple));

    //get the correct cracker binary for the benchmarks
    foreach ($versions as $version) {
      if ($benchmark->getCrackerBinaryId() == $version->getId()) {

        $benchmark->setCrackerBinaryId($version->getBinaryName() . " " . $version->getVersion());
        break;
      }
    }
}

UI::add('benchmarks', $benchmarks);
UI::add('numBenchmarks', sizeof($benchmarks));

echo Template::getInstance()->render(UI::getObjects());
