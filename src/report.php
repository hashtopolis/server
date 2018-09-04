<?php
use DBA\Factory;
use DBA\QueryFilter;
use DBA\TaskWrapper;
use DBA\OrderFilter;
use DBA\ContainFilter;
use DBA\Task;

require_once(dirname(__FILE__) . "/inc/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DAccessControl::MANAGE_HASHLIST_ACCESS);

// load hashlist
$hashlist = Factory::getHashlistFactory()->get($_GET['hashlistId']);
if($hashlist == null){
	UI::printError(UI::ERROR, "Invalid hashlist!");
}
else if(!AccessUtils::userCanAccessHashlists($hashlist, Login::getInstance()->getUser())){
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

// load report
$objects = ['hashlist' => $hashlist, 'tasks' => $tasks];
$report = $_GET['report'];
$reports = Util::scanReportDirectory();
$found = false;
foreach($reports as $r){
	if(strpos($r, "hashlist-") !== 0){
		continue;
	}
	else if(strpos(substr($r, 9, -13), $report) === 0){
		$found = $r;
	}
}
if($found === false){
	UI::printError(UI::ERROR, "Invalid report!");
}

// render report
$template = new Template("report/$r");
$baseName = dirname(__FILE__)."/tmp/".time()."-hashlist-".$hashlist->getId();
$tempName = $baseName.".tex";
file_put_contents($tempName, $template->render($objects));

sleep(1);

// create PDF
$output = [];
$cmd = "cd \"".dirname(__FILE__)."/tmp/\" && pdflatex \"".$tempName."\"";
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	$cmd = str_replace("/", "\\", $cmd);
}
exec($cmd, $output);

if(!file_exists($baseName.".pdf")){
	UI::printError(UI::ERROR, "Failed to generate PDF!");
}

// download pdf
header('Content-Type: application/octet-stream');
header("Content-disposition: attachment; filename=\"Hashlist Report " . $hashlist->getId() . "\""); 
echo file_get_contents($baseName.".pdf");

// cleanup
unlink($baseName.".aux");
unlink($baseName.".log");