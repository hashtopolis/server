<?php

use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::HASHTYPES_VIEW_PERM);

Template::loadInstance("hashtypes");
Menu::get()->setActive("config_hashtypes");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $hashtypeHandler = new HashtypeHandler();
  $hashtypeHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$hashtypes = Factory::getHashTypeFactory()->filter([]);

UI::add('hashtypes', $hashtypes);
UI::add('pageTitle', "Hashtypes");

echo Template::getInstance()->render(UI::getObjects());




