<?php

use DBA\Factory;
use DBA\StoredValue;

// set to 1 for debugging
ini_set("display_errors", "0");

session_start();

require_once(dirname(__FILE__) . "/info.php");

$INSTALL = false;
@include(dirname(__FILE__) . "/conf.php");

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

include(dirname(__FILE__) . "/mask.php");

// include DBA
require_once(dirname(__FILE__) . "/../dba/init.php");

$LANG = new Lang();
UI::add('version', $VERSION);
UI::add('host', $HOST);
UI::add('gitcommit', Util::getGitCommit());
UI::add('build', '');

// Darkmode
if (isset($_COOKIE['toggledarkmode']) && $_COOKIE['toggledarkmode'] == '1') {
  UI::add('toggledarkmode', 1);
}
else {
  UI::add('toggledarkmode', 0);
}

$updateExecuted = false;
if ($INSTALL) {
  // check if update is needed 
  // (note if the version was retrieved with git, but the git folder was removed, smaller updates are not recognized because the build value is missing)
  $storedVersion = Factory::getStoredValueFactory()->get("version");
  if ($storedVersion == null || $storedVersion->getVal() != explode("+", $VERSION)[0] && file_exists(dirname(__FILE__) . "/../install/updates/update.php")) {
    include(dirname(__FILE__) . "/../install/updates/update.php");
    $updateExecuted = $upgradePossible;
  }
  else { // in case it is not a version upgrade, but the person retrieved a new version via git or copying
    $storedBuild = Factory::getStoredValueFactory()->get("build");
    if ($storedBuild == null || ($BUILD != 'repository' && $storedBuild->getVal() != $BUILD) || ($BUILD == 'repository' && strlen(Util::getGitCommit(true)) > 0 && $storedBuild->getVal() != Util::getGitCommit(true)) && file_exists(dirname(__FILE__) . "/../install/updates/update.php")) {
      include(dirname(__FILE__) . "/../install/updates/update.php");
      $updateExecuted = $upgradePossible;
    }
  }
  
  if (strlen(Util::getGitCommit()) == 0) {
    $storedBuild = Factory::getStoredValueFactory()->get("build");
    if ($storedBuild != null) {
      UI::add('build', $storedBuild->getVal());
    }
  }
}

UI::add('menu', Menu::get());
UI::add('messages', []);

if ($updateExecuted) {
  UI::addMessage(UI::SUCCESS, "An automatic upgrade was executed! " . sizeof($EXECUTED) . " changes applied on DB!");
}

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

if ($INSTALL) {
  // CSRF setup
  CSRF::init();
}



