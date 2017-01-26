<?php

use DBA\LogEntry;
use DBA\OrderFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::ADMINISTRATOR) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("log");
$MENU->setActive("config_log");
$message = "";

$oF = new OrderFilter(LogEntry::TIME, "DESC LIMIT 100");
$entries = $FACTORIES::getLogEntryFactory()->filter(array($FACTORIES::ORDER => $oF));

$OBJECTS['entries'] = $entries;

echo $TEMPLATE->render($OBJECTS);




