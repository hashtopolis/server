<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::HEALTH_VIEW_PERM);

Template::loadInstance("health");
UI::add('pageTitle', "Health Checks");
Menu::get()->setActive("config_health");

// TODO: load

echo Template::getInstance()->render(UI::getObjects());




