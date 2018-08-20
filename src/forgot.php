<?php

require_once(dirname(__FILE__) . "/inc/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::FORGOT_VIEW_PERM);

$TEMPLATE = new Template("forgot");

if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $forgotHandler = new ForgotHandler();
  $forgotHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

UI::add('pageTitle', "Forgot Password");

echo $TEMPLATE->render(UI::getObjects());




