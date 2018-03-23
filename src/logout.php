<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */

$ACCESS_CONTROL->checkPermission(DViewControl::LOGOUT_VIEW_PERM);

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php");
  die();
}

$LOGIN->logout();

header("Location: index.php?logout=1" . time());




