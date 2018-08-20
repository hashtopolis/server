<?php

require_once(dirname(__FILE__) . "/inc/load.php");

$ACCESS_CONTROL->checkPermission(DViewControl::LOGOUT_VIEW_PERM);

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php");
  die();
}

Login::getInstance()->logout();

header("Location: index.php?logout=1" . time());




