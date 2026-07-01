<?php

/**
 * This file should only be called by docker-entrypoint.sh so that it's only executed on the docker startup
 **/

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\_sqlx_migrations;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\StoredValue;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DDirectories;
use Hashtopolis\inc\StartupConfig;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\utils\MigrationUtils;

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

/*
 * Here we would have to check what current migrations branch the setup is on (if it's not $initialSetup):
 * - check the oldest entry to identify which generation we are on
 * - check the newest entry to see if still a migration on the current generation is needed
 * - after that, we fake in the entry of the newer generation, run migration on this new generation
 * - if needed (because there are more generations available), run the previous step again
 */

if (!$initialSetup) {
  // retrieve the oldest migration
  $oF = new OrderFilter(_sqlx_migrations::VERSION, "ASC");
  $firstEntry = Factory::get_sqlx_migrationsFactory()->filter([Factory::ORDER => $oF], true);
  
  if ($firstEntry == null) {
    echo "Unable to identify migrations position!\n";
    exit(-1);
  }
  
  // identify the generation we are on
  $allGenerations = MigrationUtils::getAllGenerations(StartupConfig::getInstance()->getDatabaseType());
  $generation = -1;
  foreach ($allGenerations as $gen => $migrations) {
    if (sizeof($migrations) == 0) {
      continue;
    }
    if (explode("_", $migrations[0])[0] == $firstEntry->getId()) {
      $generation = $gen;
      break;
    }
  }
  
  if ($generation == -1) {
    echo "Could not determine current migrations generation, aborting...\n";
    exit(-1);
  }
  
  try {
    while ($generation > 0) {
      echo "Upgrading to a new sqlx migrations generation (current $generation)...\n";
      
      // we are on an older generation branch, we need to migrate
      // make sure we are up-to-date on this generation
      echo "Running migration on current generation to be up-to-date...\n";
      MigrationUtils::runDatabaseMigration($generation);
      
      // jump to next migration
      $generation--;
      $entry = MigrationUtils::getMigrationStartEntry($generation);
      if ($entry === null) {
        throw new Exception("Failed to retrieve initial migration information for generation $generation!");
      }
      
      // clear migration table
      echo "Clearing migration table...\n";
      Factory::get_sqlx_migrationsFactory()->massDeletion([]);
      
      // add first entry
      echo "Add initial migration entry...\n";
      Factory::get_sqlx_migrationsFactory()->save($entry);
      echo "Generation switch from " . ($generation + 1) . " to $generation completed!\n";
    }
  }
  catch (Exception $e) {
    echo "Failed to run generation upgrade: $e\n";
    exit(-1);
  }
}

// run database migration on current generation to be fully up-to-date
MigrationUtils::runDatabaseMigration();

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
  $email = "htp-admin@localhost.local";
  
  // load initial json data
  $objects = json_decode(file_get_contents(__DIR__ . "/setup.json"), true);
  $hashtypes = json_decode(file_get_contents(__DIR__ . "/hashtypes.json"), true);
  
  Factory::getAgentFactory()->getDB()->beginTransaction();
  
  // insert right group
  Util::checkOrCreateInitialObject(Factory::getRightGroupFactory(), $objects[Factory::getRightGroupFactory()->getModelName()][0]);
  
  $qF = new QueryFilter(RightGroup::GROUP_NAME, "Administrator", "=");
  $group = Factory::getRightGroupFactory()->filter([Factory::FILTER => $qF]);
  $group = $group[0];
  $newSalt = Util::randomString(20);
  $CIPHER = StartupConfig::getInstance()->getPepper(1) . $password . $newSalt;
  $options = array('cost' => 12);
  $newHash = password_hash($CIPHER, PASSWORD_BCRYPT, $options);
  
  $user = new User(null, $username, $email, $newHash, $newSalt, 1, 1, 0, time(), 3600, $group->getId(), 0, "", "", "", "");
  $user = Factory::getUserFactory()->save($user);
  
  // create default access group and associate admin user to it
  $group = AccessUtils::getOrCreateDefaultAccessGroup();
  $groupUser = new AccessGroupUser(null, $group->getId(), $user->getId());
  Factory::getAccessGroupUserFactory()->save($groupUser);
  
  // insert additional initial data
  $factories = [
    Factory::getConfigSectionFactory(),
    Factory::getConfigFactory(),
    Factory::getApiGroupFactory(),
    Factory::getAgentBinaryFactory(),
    Factory::getCrackerBinaryTypeFactory(),
    Factory::getCrackerBinaryFactory(),
    Factory::getPreprocessorFactory(),
    Factory::getHashTypeFactory()
  ];
  foreach ($factories as $factory) {
    foreach ($objects[$factory->getModelName()] as $object) {
      Util::checkOrCreateInitialObject($factory, $object);
    }
  }
  
  Factory::getAgentFactory()->getDB()->commit();
}

// check if directories are saved in config
Util::checkDataDirectory(DDirectories::FILES, StartupConfig::getInstance()->getDirectoryFiles());
Util::checkDataDirectory(DDirectories::IMPORT, StartupConfig::getInstance()->getDirectoryImport());
Util::checkDataDirectory(DDirectories::LOG, StartupConfig::getInstance()->getDirectoryLog());
Util::checkDataDirectory(DDirectories::CONFIG, StartupConfig::getInstance()->getDirectoryConfig());
Util::checkDataDirectory(DDirectories::TUS, StartupConfig::getInstance()->getDirectoryTus());