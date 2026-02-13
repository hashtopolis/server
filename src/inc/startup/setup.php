<?php

/**
 * This file should only be called by docker-entrypoint.sh so that it's only executed on the docker startup
 **/

use DBA\AccessGroupUser;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\RightGroup;
use DBA\StoredValue;
use DBA\User;

// set to 1 for debugging
ini_set("display_errors", "0");

session_start();

require_once(dirname(__FILE__) . "/include.php");

// create directories if not exists and ensure they are writeable
foreach (StartupConfig::getInstance()->getDirectories() as $name => $path) {
  if (!file_exists($path)) {
    if (mkdir($path) === false) {
      die("Unable to create directory '$path'!");
    }
  }
  elseif (!is_writable($path)) {
    die("Directory '$path' is not writable!");
  }
}

// check if the system is set up and installed
if (Factory::getUserFactory()->getDB() === null) {
  //connection not valid
  die("Database connection failed!");
}
$initialSetup = false;
try {
  Factory::getAgentFactory()->filter([], true);
}
catch (PDOException $e) {
  // initial setup, run only on the very first time
  // the boolean is stored to later when the database is migrated, some initial queries can be done
  $initialSetup = true;
}

// this only needs to be present for the very first upgrade from non-migration to migrations to make sure the last updates are executed before migration
if (!$initialSetup && StartupConfig::getInstance()->getDatabaseType() == "mysql" && !Util::databaseTableExists("_sqlx_migrations")) {
  include(dirname(__FILE__) . "/../../install/updates/update.php");
}

$database_uri = StartupConfig::getInstance()->getDatabaseType() . "://" . StartupConfig::getInstance()->getDatabaseUser() . ":" . StartupConfig::getInstance()->getDatabasePassword() . "@" . StartupConfig::getInstance()->getDatabaseServer() . ":" . StartupConfig::getInstance()->getDatabasePort() . "/" . StartupConfig::getInstance()->getDatabaseDB();
exec('/usr/bin/sqlx migrate run --source ' . dirname(__FILE__) . '/../../migrations/' . StartupConfig::getInstance()->getDatabaseType() . '/ -D ' . $database_uri, $output, $retval);
if ($retval !== 0) {
  die("Failed to run migrations: \n" . implode("\n", $output));
}

if ($initialSetup === true) {
  // if peppers are not set, generate them and save them
  if (strlen(StartupConfig::getInstance()->getPepper(0)) == 0) {
    $pepper = [
      Util::randomString(32),
      Util::randomString(32),
      Util::randomString(32),
      Util::randomString(32)
    ];
    
    $json_config_filepath = StartupConfig::getInstance()->getDirectoryConfig() . "/config.json";
    if (file_put_contents($json_config_filepath, json_encode(array('PEPPER' => $pepper))) === false) {
      die("Cannot write configuration file '$json_config_filepath'!");
    }
    StartupConfig::reload();
  }
  
  // save version and build
  $version = new StoredValue("version", explode("+", StartupConfig::getInstance()->getVersion())[0]);
  Factory::getStoredValueFactory()->save($version);
  $build = new StoredValue("build", StartupConfig::getInstance()->getBuild());
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
  $CIPHER = StartupConfig::getInstance()->getPepper(1) . $password . $newSalt;
  $options = array('cost' => 12);
  $newHash = password_hash($CIPHER, PASSWORD_BCRYPT, $options);
  
  $user = new User(null, $username, $email, $newHash, $newSalt, 1, 1, 0, time(), 3600, $group->getId(), 0, "", "", "", "");
  $user = Factory::getUserFactory()->save($user);
  
  // create default group
  $group = AccessUtils::getOrCreateDefaultAccessGroup();
  $groupUser = new AccessGroupUser(null, $group->getId(), $user->getId());
  Factory::getAccessGroupUserFactory()->save($groupUser);
  
  Factory::getAgentFactory()->getDB()->commit();
}

// check if directories are saved in config
Util::checkDataDirectory(DDirectories::FILES, StartupConfig::getInstance()->getDirectoryFiles());
Util::checkDataDirectory(DDirectories::IMPORT, StartupConfig::getInstance()->getDirectoryImport());
Util::checkDataDirectory(DDirectories::LOG, StartupConfig::getInstance()->getDirectoryLog());
Util::checkDataDirectory(DDirectories::CONFIG, StartupConfig::getInstance()->getDirectoryConfig());
Util::checkDataDirectory(DDirectories::TUS, StartupConfig::getInstance()->getDirectoryTus());