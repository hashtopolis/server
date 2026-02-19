<?php

use DBA\LogEntry;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::LOG_VIEW_PERM);

Template::loadInstance("log");
UI::add('pageTitle', "Log");
Menu::get()->setActive("config_log");

$level = "0";
if (isset($_POST['show'])) {
  $level = $_POST['level'];
}
UI::add('level', $level);

$qF = new QueryFilter(LogEntry::LEVEL, $level, "=");
$oF = new OrderFilter(LogEntry::TIME, "DESC LIMIT 100");

$filter = array(Factory::ORDER => $oF);
if ($level !== "0") {
  $filter[Factory::FILTER] = $qF;
}

$entries = Factory::getLogEntryFactory()->filter($filter);
UI::add('entries', $entries);

echo Template::getInstance()->render(UI::getObjects());




