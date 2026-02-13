<?php

use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\Config;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\CSRF;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\handlers\ConfigHandler;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\Menu;
use Hashtopolis\inc\templating\Template;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessControl;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::CONFIG_VIEW_PERM);

Template::loadInstance("config");
Menu::get()->setActive("config_server");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $configHandler = new ConfigHandler();
  $configHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$configuration = array();
$configSectionId = (isset($_GET['view'])) ? $_GET['view'] : 1;
$qF = new QueryFilter(Config::CONFIG_SECTION_ID, $configSectionId, "=");
$entries = Factory::getConfigFactory()->filter([Factory::FILTER => $qF]);
UI::add('configSectionId', 0);
foreach ($entries as $entry) {
  $set = new DataSet();
  $set->addValue('item', $entry->getItem());
  $set->addValue('value', $entry->getValue());
  $configuration[] = $set;
  UI::add('configSectionId', $configSectionId);
}

UI::add('pageTitle', "Configuration");
UI::add('configuration', $configuration);

echo Template::getInstance()->render(UI::getObjects());




