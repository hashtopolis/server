<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */
/** @var DataSet $CONFIG */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::ADMINISTRATOR) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("config");
$MENU->setActive("config_server");

//catch actions here...
if (isset($_POST['action'])) {
  $configHandler = new ConfigHandler();
  $configHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$configuration = array();
$all = $CONFIG->getAllValues();
foreach ($all as $key => $value) {
  $set = new DataSet();
  $set->addValue('item', $key);
  $set->addValue('value', $value);
  $configuration[] = $set;
}

$OBJECTS['configuration'] = $configuration;

echo $TEMPLATE->render($OBJECTS);




