<?php

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

$TEMPLATE = new Template("hashtypes");
$MENU->setActive("config_hashtypes");
$message = "";

//catch actions here...
if (isset($_POST['action'])) {
  $hashtypeHandler = new HashtypeHandler();
  $hashtypeHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$hashtypes = $FACTORIES::getHashTypeFactory()->filter(array());

$OBJECTS['hashtypes'] = $hashtypes;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




