<?php

use DBA\LogEntry;
use DBA\OrderFilter;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkViewPermission(DViewControl::LOG_VIEW_PERM);

$TEMPLATE = new Template("log");
$OBJECTS['pageTitle'] = "Log";
$MENU->setActive("config_log");

$level = "0";
if (isset($_POST['show'])) {
  $level = $_POST['level'];
}
$OBJECTS['level'] = $level;

$qF = new QueryFilter(LogEntry::LEVEL, $level, "=");
$oF = new OrderFilter(LogEntry::TIME, "DESC LIMIT 100");

$filter = array($FACTORIES::ORDER => $oF);
if ($level !== "0") {
  $filter[$FACTORIES::FILTER] = $qF;
}

$entries = $FACTORIES::getLogEntryFactory()->filter($filter);
$OBJECTS['entries'] = $entries;

echo $TEMPLATE->render($OBJECTS);




