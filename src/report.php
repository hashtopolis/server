<?php

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\templating\Template;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessControl;
use Hashtopolis\inc\utils\AccessUtils;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$objects = [];
$type = "";
$typeId = 0;
$filename = "gen.pdf";
if (isset($_GET['hashlistId'])) {
  AccessControl::getInstance()->checkPermission(DAccessControl::MANAGE_HASHLIST_ACCESS);
  
  // load hashlist
  $hashlist = Factory::getHashlistFactory()->get($_GET['hashlistId']);
  if ($hashlist == null) {
    UI::printError(UI::ERROR, "Invalid hashlist!");
  }
  else if (!AccessUtils::userCanAccessHashlists($hashlist, Login::getInstance()->getUser())) {
    UI::printError(UI::ERROR, "No access to hashlist!");
  }
  
  // load task wrappers
  $qF = new QueryFilter(TaskWrapper::HASHLIST_ID, $hashlist->getId(), "=");
  $oF = new OrderFilter(TaskWrapper::TASK_WRAPPER_ID, "ASC");
  $taskWrappers = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
  
  // load tasks
  $qF = new ContainFilter(Task::TASK_WRAPPER_ID, Util::arrayOfIds($taskWrappers));
  $oF = new OrderFilter(Task::TASK_ID, "ASC");
  $tasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
  
  // load other data
  $hashtype = Factory::getHashTypeFactory()->get($hashlist->getHashTypeId());
  
  // set settings
  $objects = ['hashlist' => $hashlist, 'tasks' => $tasks, 'hashtype' => $hashtype];
  $type = "hashlist";
  $typeId = $hashlist->getId();
  $filename = "Hashlist_Report_" . $hashlist->getId() . ".pdf";
}
else if (isset($_GET['checkId'])) {
  AccessControl::getInstance()->checkPermission(DAccessControl::SERVER_CONFIG_ACCESS);
  
  // load health check
  $check = Factory::getHealthCheckFactory()->get($_GET['checkId']);
  if ($check == null) {
    UI::printError(UI::ERROR, "Invalid health check!");
  }
  
  // load agent checks
  $qF = new QueryFilter(HealthCheckAgent::HEALTH_CHECK_ID, $check->getId(), "=");
  $agentChecks = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => $qF]);
  
  // load agent data
  $agents = Factory::getAgentFactory()->filter([]);
  $agentData = new DataSet();
  foreach ($agents as $agent) {
    $agentData->addValue($agent->getId(), $agent);
  }
  
  // set settings
  $objects = ['check' => $check, 'agentChecks' => $agentChecks, 'agents' => $agentData];
  $type = "health-check";
  $typeId = $check->getId();
  $filename = "Health_Check_Report_" . $check->getId() . ".pdf";
}
else {
  UI::printError(UI::ERROR, "Invalid request!");
}

// load report
$report = $_GET['report'];
$reports = Util::scanReportDirectory();
$found = false;
foreach ($reports as $r) {
  if (strpos($r, $type . "-") !== 0) {
    continue;
  }
  else if (strpos(substr($r, strlen($type) + 1, -13), $report) === 0) {
    $found = $r;
  }
}
if ($found === false) {
  UI::printError(UI::ERROR, "Invalid report!");
}

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  if (!`where pdflatex`) {
    UI::printError(UI::ERROR, "LaTeX needs to be installed to use this feature!");
  }
}
else {
  if (!`which pdflatex`) {
    UI::printError(UI::ERROR, "LaTeX needs to be installed to use this feature!");
  }
}

// render report
$template = new Template("report/$found");
$baseName = "/tmp/" . time() . "-$type-" . $typeId;
$tempName = $baseName . ".tex";
file_put_contents($tempName, $template->render($objects));

sleep(1);

// create PDF
$output = [];
$cmd = "cd \"/tmp/\" && pdflatex \"" . $tempName . "\"";
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $cmd = str_replace("/", "\\", $cmd);
}
exec($cmd, $output);

if (!file_exists($baseName . ".pdf")) {
  UI::printError(UI::ERROR, "Failed to generate PDF!");
}

// download pdf
header('Content-Type: application/octet-stream');
header("Content-disposition: attachment; filename=\"$filename\"");
echo file_get_contents($baseName . ".pdf");

// cleanup
unlink($baseName . ".aux");
unlink($baseName . ".log");
