<?php
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::ACCOUNT_VIEW_PERM);

Template::loadInstance("account");
Menu::get()->setActive("account_settings");
UI::add('pageTitle', "Account Settings");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $accountHandler = new AccountHandler(Login::getInstance()->getUserID());
  $accountHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$group = Factory::getRightGroupFactory()->get(Login::getInstance()->getUser()->getRightGroupId());
UI::add('group', $group);

echo Template::getInstance()->render(UI::getObjects());




