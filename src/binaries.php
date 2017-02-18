<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var array $OBJECTS */
/** @var Login $LOGIN */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::ADMINISTRATOR) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("binaries");
$MENU->setActive("config_binaries");

//catch actions here...
if (isset($_POST['action'])) {
  $binaryHandler = new AgentBinaryHandler();
  $binaryHandler->handle($_POST['action']);
  Util::refresh();
}

$binaries = $FACTORIES::getAgentBinaryFactory()->filter(array());
$OBJECTS['binaries'] = $binaries;

echo $TEMPLATE->render($OBJECTS);




