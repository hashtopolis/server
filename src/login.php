<?php

require_once(dirname(__FILE__) . "/inc/startup/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::LOGIN_VIEW_PERM);

if (!isset($_POST['username']) || !isset($_POST['password'])) {
  header("Location: index.php?err=1" . time());
  die();
}

$username = $_POST['username'];
$password = $_POST['password'];

// isYubikeyEnabled() ?
$otp = (isset($_POST['otp'])) ? $_POST['otp'] : "";
$fw = (isset($_POST['fw'])) ? $_POST['fw'] : "";

if (strlen($username) == 0 || strlen($password) == 0) {
  header("Location: index.php?err=2" . time());
  die();
}

Login::getInstance()->login($username, $password, $otp);

if (Login::getInstance()->isLoggedin()) {
  if (strlen($fw) > 0) {
    $fw = urldecode($fw);
    $url = Util::buildServerUrl() . ((Util::startsWith($fw, '/')) ? "" : "/") . $fw;
    header("Location: " . $url);
    die();
  }
  header("Location: index.php");
  die();
}

header("Location: index.php?err=3" . time());




