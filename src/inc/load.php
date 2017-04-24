<?php

use DBA\Factory;

//set to 1 for debugging
ini_set("display_errors", "0");

$OBJECTS = array();

$VERSION = "0.3.2";
$HOST = @$_SERVER['HTTP_HOST'];
if (strpos($HOST, ":") !== false) {
  $HOST = substr($HOST, 0, strpos($HOST, ":"));
}

$OBJECTS['version'] = $VERSION;
$OBJECTS['host'] = $HOST;

$INSTALL = false;
@include(dirname(__FILE__)."/db.php");

// include all .class.php files in inc dir
$dir = scandir(dirname(__FILE__));
foreach ($dir as $entry) {
  if (strpos($entry, ".class.php") !== false) {
    require_once(dirname(__FILE__) . "/" . $entry);
  }
}
require_once(dirname(__FILE__) . "/templating/Statement.class.php");
require_once(dirname(__FILE__) . "/templating/Template.class.php");

// include all handlers
require_once(dirname(__FILE__) . "/handlers/Handler.class.php");
$dir = scandir(dirname(__FILE__) . "/handlers/");
foreach ($dir as $entry) {
  if (strpos($entry, ".class.php") !== false) {
    require_once(dirname(__FILE__) . "/handlers/" . $entry);
  }
}

// DEFINES
include(dirname(__FILE__) . "/defines.php");
include(dirname(__FILE__) . "/protocol.php");

// include notifications
$NOTIFICATIONS = array();
require_once(dirname(__FILE__) . "/notifications/Notification.class.php");
$dir = scandir(dirname(__FILE__) . "/notifications/");
foreach ($dir as $entry) {
  if (strpos($entry, ".class.php") !== false) {
    require_once(dirname(__FILE__) . "/notifications/" . $entry);
  }
}

// include DBA
require_once(dirname(__FILE__) . "/../dba/init.php");

$FACTORIES = new Factory();
$LANG = new Lang();

$gitcommit = "";
$gitfolder = dirname(__FILE__) . "/../../.git";
if (file_exists($gitfolder) && is_dir($gitfolder)) {
  $head = file_get_contents($gitfolder . "/HEAD");
  $branch = trim(substr($head, strlen("ref: refs/heads/"), -1));
  $commit = trim(file_get_contents($gitfolder . "/refs/heads/" . $branch));
  $gitcommit = "commit " . substr($commit, 0, 7) . " branch $branch";
}
$OBJECTS['gitcommit'] = $gitcommit;

$LOGIN = null;
$MENU = new Menu();
$OBJECTS['menu'] = $MENU;
$OBJECTS['messages'] = array();
if ($INSTALL) {
  $LOGIN = new Login();
  $OBJECTS['login'] = $LOGIN;
  if ($LOGIN->isLoggedin()) {
    $OBJECTS['user'] = $LOGIN->getUser();
  }
  
  $res = $FACTORIES::getConfigFactory()->filter(array());
  $CONFIG = new DataSet();
  foreach ($res as $entry) {
    $CONFIG->addValue($entry->getItem(), $entry->getValue());
  }
  $OBJECTS['config'] = $CONFIG;
  
  //set autorefresh to false for all pages
  $OBJECTS['autorefresh'] = -1;
}


