<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}

$TEMPLATE = new Template("account");
$MENU->setActive("account");
$message = "";

//catch actions here...
if (isset($_POST['action'])) {
  $accountHandler = new AccountHandler($LOGIN->getUserID());
  $accountHandler->handle($_POST['action']);
}

$group = $FACTORIES::getRightGroupFactory()->get($LOGIN->getUser()->getRightGroupId());

$OBJECTS['group'] = $group;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




