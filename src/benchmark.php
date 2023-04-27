<?php
use DBA\QueryFilter;
use DBA\Factory;

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

foreach ($benchmarks as $benchmark) {
  $devices = HardwareGroupUtils::getDevicesFromBenchmark($benchmark);
  $benchmark->setHardwareGroupId($devices);
}


UI::add('benchmarks', $benchmarks);
UI::add('numBenchmarks', sizeof($benchmarks));

echo Template::getInstance()->render(UI::getObjects());

?>