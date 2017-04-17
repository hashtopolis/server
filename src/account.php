<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$TEMPLATE = new Template("account");
$MENU->setActive("account_settings");

//catch actions here...
if (isset($_POST['action'])) {
  $accountHandler = new AccountHandler($LOGIN->getUserID());
  $accountHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$group = $FACTORIES::getRightGroupFactory()->get($LOGIN->getUser()->getRightGroupId());
$OBJECTS['group'] = $group;

echo $TEMPLATE->render($OBJECTS);




