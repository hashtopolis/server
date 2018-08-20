<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var array $OBJECTS */

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::HASHTYPES_VIEW_PERM);

$TEMPLATE = new Template("hashtypes");
$MENU->setActive("config_hashtypes");
$message = "";

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $hashtypeHandler = new HashtypeHandler();
  $hashtypeHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$hashtypes = Factory::getHashTypeFactory()->filter([]);

$OBJECTS['hashtypes'] = $hashtypes;
$OBJECTS['message'] = $message;
$OBJECTS['pageTitle'] = "Hashtypes";

echo $TEMPLATE->render($OBJECTS);




