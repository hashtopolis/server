<?php

use Hashtopolis\inc\CSRF;
use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\handlers\ForgotHandler;
use Hashtopolis\inc\templating\Template;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessControl;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::FORGOT_VIEW_PERM);

Template::loadInstance("forgot");

if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $forgotHandler = new ForgotHandler();
  $forgotHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

UI::add('pageTitle', "Forgot Password");

echo Template::getInstance()->render(UI::getObjects());




