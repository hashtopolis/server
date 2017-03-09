<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']));
  die();
}

$TEMPLATE = new Template("notifications");
$MENU->setActive("account_notifications");

//catch actions here...
if (isset($_POST['action'])) {
  $notificationHandler = new NotificationHandler();
  $notificationHandler->handle($_POST['action']);
  if(UI::getNumMessages() == 0){
    Util::refresh();
  }
}

//TODO: load here

echo $TEMPLATE->render($OBJECTS);




