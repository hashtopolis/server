<?php

// set to 1 for debugging
ini_set("display_errors", "0");

session_start();

$VERSION = "0.8.0";
$HOST = @$_SERVER['HTTP_HOST'];
if (strpos($HOST, ":") !== false) {
  $HOST = substr($HOST, 0, strpos($HOST, ":"));
}

$INSTALL = false;
@include(dirname(__FILE__) . "/db.php");

// include all .class.php files in inc dir
$dir = scandir(dirname(__FILE__));
foreach ($dir as $entry) {
  if (strpos($entry, ".class.php") !== false) {
    require_once(dirname(__FILE__) . "/" . $entry);
  }
}
require_once(dirname(__FILE__) . "/templating/Statement.class.php");
require_once(dirname(__FILE__) . "/templating/Template.class.php");

// include all required files
require_once(dirname(__FILE__) . "/handlers/Handler.class.php");
require_once(dirname(__FILE__) . "/notifications/Notification.class.php");
require_once(dirname(__FILE__) . "/api/APIBasic.class.php");
require_once(dirname(__FILE__) . "/user-api/UserAPIBasic.class.php");
$directories = array('handlers', 'api', 'defines', 'utils', 'notifications', 'user-api');
foreach ($directories as $directory) {
  $dir = scandir(dirname(__FILE__) . "/$directory/");
  foreach ($dir as $entry) {
    if (strpos($entry, ".php") !== false) {
      require_once(dirname(__FILE__) . "/$directory/" . $entry);
    }
  }
}

include(dirname(__FILE__) . "/protocol.php");

// include DBA
require_once(dirname(__FILE__) . "/../dba/init.php");

$LANG = new Lang();
UI::add('version', $VERSION);
UI::add('host', $HOST);

$gitcommit = "";
$gitfolder = dirname(__FILE__) . "/../../.git";
if (file_exists($gitfolder) && is_dir($gitfolder)) {
  $head = file_get_contents($gitfolder . "/HEAD");
  $branch = trim(substr($head, strlen("ref: refs/heads/"), -1));
  if (file_exists($gitfolder . "/refs/heads/" . $branch)) {
    $commit = trim(file_get_contents($gitfolder . "/refs/heads/" . $branch));
    $gitcommit = "commit " . substr($commit, 0, 7) . " branch $branch";
  }
  else {
    $commit = $head;
    $gitcommit = "commit " . substr($commit, 0, 7);
  }
}
UI::add('gitcommit', $gitcommit);

UI::add('menu', Menu::get());
UI::add('messages', []);
UI::add('pageTitle', "");
if ($INSTALL) {
  UI::add('login', Login::getInstance());
  if (Login::getInstance()->isLoggedin()) {
    UI::add('user', Login::getInstance()->getUser());
    AccessControl::getInstance(Login::getInstance()->getUser());
  }
  
  UI::add('config', SConfig::getInstance());
  
  define("APP_NAME", (SConfig::getInstance()->getVal(DConfig::S_NAME) == 1) ? "Hashtopussy" : "Hashtopolis");
  
  //set autorefresh to false for all pages
  UI::add('autorefresh', -1);
}
UI::add('accessControl', AccessControl::getInstance());

// CSRF setup
CSRF::init();



