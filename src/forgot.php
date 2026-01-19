<?php

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




