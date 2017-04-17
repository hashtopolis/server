<?php

use DBA\HashcatRelease;
use DBA\OrderFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::USER) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("hashcat/index");
$MENU->setActive("hashcat_list");

//catch actions here...
if (isset($_POST['action'])) {
  $hashcatHandler = new HashcatHandler();
  $hashcatHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new'])) {
  $TEMPLATE = new Template("hashcat/new");
  $MENU->setActive("hashcat_new");
  $oF = new OrderFilter(HashcatRelease::TIME, "DESC LIMIT 1");
  $releases = $FACTORIES::getHashcatReleaseFactory()->filter(array($FACTORIES::ORDER => $oF));
  $rootDir = "";
  $common = "";
  if (sizeof($releases) > 0) {
    $rootDir = $releases[0]->getRootdir();
  }
  $OBJECTS['rootDir'] = $rootDir;
}
else {
  $oF = new OrderFilter(HashcatRelease::TIME, "DESC");
  $OBJECTS['releases'] = $FACTORIES::getHashcatReleaseFactory()->filter(array($FACTORIES::ORDER => $oF));
}

echo $TEMPLATE->render($OBJECTS);




