<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['otp'])) {
  header("Location: index.php?err=1" . time());
  die();
}

$username = $_POST['username'];
$password = $_POST['password'];

// isYubikeyEnabled() ?
$otp = (isset($_POST['otp'])) ? $_POST['otp'] : "";
$fw  = (isset($_POST['fw']))  ? $_POST['fw']  : "";

if (strlen($username) == 0 || strlen($password) == 0) {
  header("Location: index.php?err=2" . time());
  die();
}

$LOGIN->login($username, $password, $otp);

if ($LOGIN->isLoggedin()) {
  if (strlen($fw) > 0) {
    header("Location: " . Util::buildServerUrl() . "/" . urldecode($fw));
    die();
  }
  header("Location: index.php");
  die();
}

header("Location: index.php?err=3" . time());




