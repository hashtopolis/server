<?php
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var array $OBJECTS */

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::ACCOUNT_VIEW_PERM);

$TEMPLATE = new Template("account");
$MENU->setActive("account_settings");
$OBJECTS['pageTitle'] = "Account Settings";

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $accountHandler = new AccountHandler(Login::getInstance()->getUserID());
  $accountHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$group = Factory::getRightGroupFactory()->get(Login::getInstance()->getUser()->getRightGroupId());
$OBJECTS['group'] = $group;

echo $TEMPLATE->render($OBJECTS);




