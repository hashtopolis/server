<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 40) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("config");
$MENU->setActive("config_server");

//catch actions here...
if (isset($_POST['action'])) {
  $configHandler = new ConfigHandler();
  $configHandler->handle($_POST['action']);
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




