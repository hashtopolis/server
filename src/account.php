<?php

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\APiKey;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\CSRF;
use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\handlers\AccountHandler;
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

$qF = new QueryFilter(ApiKey::USER_ID, Login::getInstance()->getUserID(), "=");
$apiKeys = Factory::getApiKeyFactory()->filter([Factory::FILTER => $qF]);
UI::add('keys', $apiKeys);

echo Template::getInstance()->render(UI::getObjects());




