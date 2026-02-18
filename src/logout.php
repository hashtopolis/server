<?php

use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\utils\AccessControl;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::LOGOUT_VIEW_PERM);

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php");
  die();
}

Login::getInstance()->logout();

header("Location: index.php?logout=1" . time());




