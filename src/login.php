<?php
require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!isset($_POST['username']) || !isset($_POST['password'])) {
  header("Location: index.php?err=1" . time());
  die();
}

$username = $_POST['username'];
$password = $_POST['password'];
$fw = "";
if (isset($_POST['fw'])) {
  $fw = $_POST['fw'];
}

if (strlen($username) == 0 || strlen($password) == 0) {
  header("Location: index.php?err=2" . time());
  die();
}

$LOGIN->login($username, $password);

if ($LOGIN->isLoggedin()) {
  if (strlen($fw) > 0) {
    header("Location: " . urldecode($fw));
    die();
  }
  header("Location: index.php");
  die();
}

header("Location: index.php?err=3" . time());




