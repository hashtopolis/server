<?php

use DBA\QueryFilter;
use DBA\Config;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

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
if (isset($_POST['action']) && Util::checkCSRF($_POST['csrf'])) {
  $configHandler = new ConfigHandler();
  $configHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$configuration = array();
$configSectionId = (isset($_GET['view'])) ? $_GET['view'] : 1;
$qF = new QueryFilter(Config::CONFIG_SECTION_ID, $_GET['view'], "=");
$entries = $FACTORIES::getConfigFactory()->filter(array($FACTORIES::FILTER => $qF));
foreach ($entries as $entry) {
  $set = new DataSet();
  $set->addValue('item', $entry->getItem());
  $set->addValue('value', $entry->getValue());
  $configuration[] = $set;
}

$OBJECTS['configuration'] = $configuration;

echo $TEMPLATE->render($OBJECTS);




