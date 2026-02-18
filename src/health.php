<?php

use DBA\Factory;
use DBA\OrderFilter;
use DBA\HealthCheck;
use DBA\CrackerBinary;
use DBA\QueryFilter;
use DBA\HealthCheckAgent;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::HEALTH_VIEW_PERM);

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $healthHandler = new HealthHandler();
  $healthHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

Template::loadInstance("health/index");
UI::add('pageTitle', "Health Checks");
Menu::get()->setActive("config_health");

if (isset($_GET['id'])) {
  $healthCheck = Factory::getHealthCheckFactory()->get($_GET['id']);
  if ($healthCheck == null) {
    UI::printError(UI::ERROR, "Invalid health check ID!");
  }
  Template::loadInstance("health/detail");
  UI::add('check', $healthCheck);
  $qF = new QueryFilter(HealthCheckAgent::HEALTH_CHECK_ID, $healthCheck->getId(), "=");
  $checkAgents = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => $qF]);
  UI::add('checkAgents', $checkAgents);
  $agents = Factory::getAgentFactory()->filter([]);
  $agentSet = new DataSet();
  foreach ($agents as $agent) {
    $agentSet->addValue($agent->getId(), $agent);
  }
  UI::add('agentSet', $agentSet);
}
else {
  $oF = new OrderFilter(HealthCheck::TIME, "DESC");
  UI::add('checks', Factory::getHealthCheckFactory()->filter([Factory::ORDER => $oF]));
  
  // load cracker info
  $oF = new OrderFilter(CrackerBinary::CRACKER_BINARY_ID, "DESC");
  UI::add('binaries', Factory::getCrackerBinaryTypeFactory()->filter([]));
  $versions = Factory::getCrackerBinaryFactory()->filter([Factory::ORDER => $oF]);
  usort($versions, ["Util", "versionComparisonBinary"]);
  UI::add('versions', $versions);
}

echo Template::getInstance()->render(UI::getObjects());




