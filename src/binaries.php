<?php
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var array $OBJECTS */
/** @var Login $LOGIN */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::BINARIES_VIEW_PERM);

$OBJECTS['newBinary'] = false;
$OBJECTS['editBinary'] = false;

$TEMPLATE = new Template("binaries");
$MENU->setActive("config_binaries");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $binaryHandler = new AgentBinaryHandler();
  $binaryHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}
$OBJECTS['pageTitle'] = "Agent Binaries";
if (isset($_GET['new'])) {
  $OBJECTS['newBinary'] = true;
  $OBJECTS['pageTitle'] = "New Agent Binary";
}
else if (isset($_GET['edit'])) {
  $bin = Factory::getAgentBinaryFactory()->get($_GET['edit']);
  if ($bin == null) {
    UI::printError("ERROR", "Invalid agent binary ID!");
  }
  $OBJECTS['pageTitle'] = "Edit Agent Binary of type " . $bin->getType();
  $OBJECTS['editBinary'] = true;
  $OBJECTS['bin'] = $bin;
}
$binaries = Factory::getAgentBinaryFactory()->filter([]);
$OBJECTS['binaries'] = $binaries;

echo $TEMPLATE->render($OBJECTS);




