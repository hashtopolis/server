<?php

use DBA\AccessGroupUser;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\RightGroup;
use DBA\StoredValue;
use DBA\User;

// set to 1 for debugging
ini_set("display_errors", "0");

session_start();

require_once(dirname(__FILE__) . "/info.php");

include(dirname(__FILE__) . "/confv2.php");

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

// create directories if not exists and ensure they are writeable
foreach ($DIRECTORIES as $name => $path) {
  if (!file_exists($path)) {
    if (mkdir($path) === false) {
      die("Unable to create directory '$path'!");
    }
  } elseif (!is_writable($path)) {
    die("Directory '$path' is not writable!");
  }
}

// check if the system is set up and installed
if (Factory::getUserFactory()->getDB(true) === null) {
  //connection not valid
  die("Database connection failed!");
}
try {
  Factory::getUserFactory()->filter([], true);
}
catch (PDOException $e) {
  $query = file_get_contents(dirname(__FILE__) . "/../install/hashtopolis.sql");
  Factory::getAgentFactory()->getDB()->query($query);
  
  // determine the base url
  $baseUrl = explode("/", $_SERVER['REQUEST_URI']);
  unset($baseUrl[sizeof($baseUrl) - 1]);
  try {
    $urlConfig = ConfigUtils::get(DConfig::BASE_URL);
  }
  catch (HTException $e) {
    die("Failure in config: " . $e->getMessage());
  }
  $urlConfig->setValue(implode("/", $baseUrl));
  Factory::getConfigFactory()->update($urlConfig);
  
  // if peppers are not set, generate them and save them
  if (!isset($PEPPER)) {
    $PEPPER = [
      Util::randomString(32),
      Util::randomString(32),
      Util::randomString(32),
      Util::randomString(32)
    ];

    $json_config_filepath = $DIRECTORIES['config'] . "/config.json";
    if (file_put_contents($json_config_filepath, json_encode(array('PEPPER' =>$PEPPER))) === false) {
      die("Cannot write configuration file '$json_config_filepath'!");
    }
  }
  
  // save version and build
  $version = new StoredValue("version", explode("+", $VERSION)[0]);
  Factory::getStoredValueFactory()->save($version);
  $build = new StoredValue("build", $BUILD);
  Factory::getStoredValueFactory()->save($build);
  
  // create default user
  $username = "admin";
  if (getenv('HASHTOPOLIS_ADMIN_USER') !== false) {
    $username = getenv('HASHTOPOLIS_ADMIN_USER');
  }
  $password = "hashtopolis";
  if (getenv('HASHTOPOLIS_ADMIN_PASSWORD') !== false) {
    $password = getenv('HASHTOPOLIS_ADMIN_PASSWORD');
  }
  $email = "admin@localhost";
  
  Factory::getAgentFactory()->getDB()->beginTransaction();
  
  $qF = new QueryFilter(RightGroup::GROUP_NAME, "Administrator", "=");
  $group = Factory::getRightGroupFactory()->filter([Factory::FILTER => $qF]);
  $group = $group[0];
  $newSalt = Util::randomString(20);
  $CIPHER = $PEPPER[1] . $password . $newSalt;
  $options = array('cost' => 12);
  $newHash = password_hash($CIPHER, PASSWORD_BCRYPT, $options);
  
  $user = new User(null, $username, $email, $newHash, $newSalt, 1, 1, 0, time(), 3600, $group->getId(), 0, "", "", "", "");
  Factory::getUserFactory()->save($user);
  
  // create default group
  $group = AccessUtils::getOrCreateDefaultAccessGroup();
  $groupUser = new AccessGroupUser(null, $group->getId(), $user->getId());
  Factory::getAccessGroupUserFactory()->save($groupUser);
  
  Factory::getAgentFactory()->getDB()->commit();
}

// check if directories are saved in config
Util::checkDataDirectory(DDirectories::FILES, $DIRECTORIES['files']);
Util::checkDataDirectory(DDirectories::IMPORT, $DIRECTORIES['import']);
Util::checkDataDirectory(DDirectories::LOG, $DIRECTORIES['log']);
Util::checkDataDirectory(DDirectories::CONFIG, $DIRECTORIES['config']);

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

UI::add('menu', Menu::get());
UI::add('messages', []);

if ($updateExecuted) {
  UI::addMessage(UI::SUCCESS, "An automatic upgrade was executed! " . sizeof($EXECUTED) . " changes applied on DB!");
}

UI::add('pageTitle', "");
UI::add('login', Login::getInstance());
if (Login::getInstance()->isLoggedin()) {
  UI::add('user', Login::getInstance()->getUser());
  AccessControl::getInstance(Login::getInstance()->getUser());
}

UI::add('config', SConfig::getInstance());

define("APP_NAME", (SConfig::getInstance()->getVal(DConfig::S_NAME) == 1) ? "Hashtopussy" : "Hashtopolis");

//set autorefresh to false for all pages
UI::add('autorefresh', -1);

UI::add('accessControl', AccessControl::getInstance());

// CSRF setup
CSRF::init();
