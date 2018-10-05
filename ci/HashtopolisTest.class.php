<?php
use DBA\Factory;
use DBA\User;
use DBA\ApiKey;
use DBA\AccessGroupUser;

abstract class HashtopolisTest{
  protected $minVersion;
  protected $maxVersion;
  protected $runType;

  protected static $status = 0;
  protected static $testCount = 0;

  protected $user;
  protected $apiKey;

  const RUN_FULL = 0;
  const RUN_FAST = 1;

  public function initAndUpgrade($fromVersion){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initialize old version $fromVersion...");
    $this->init($fromVersion);

    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running upgrades...");
    switch($fromVersion){
      case "0.8.0":
        HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Apply 0.8.0+dev...");
        include("/var/www/html/hashtopolis/src/install/updates/update_v0.8.0_v0.x.x.php");
    }
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initialization with upgrade done!");
  }

  public function init($version){
    // drop old data and create empty DB
    Factory::getAgentFactory()->getDB()->query("DROP DATABASE IF EXISTS hashtopolis");
    Factory::getAgentFactory()->getDB()->query("CREATE DATABASE hashtopolis");
    Factory::getAgentFactory()->getDB()->query("USE hashtopolis");

    // load DB
    if($version == "master"){
      Factory::getAgentFactory()->getDB()->query(file_get_contents("/var/www/html/hashtopolis/src/install/hashtopolis.sql"));
    }
    else{
      Factory::getAgentFactory()->getDB()->query(file_get_contents(dirname(__FILE__)."/files/db_".$version.".sql"));
    }

    sleep(1);

    // insert user and api key
    $this->user = new User(null, 'testuser', '', '', '', 1, 0, 0, 0, 3600, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), 0, '', '', '', '');
    $this->user = Factory::getUserFactory()->save($this->user);
    $accessGroup = new AccessGroupUser(null, 1, $this->user->getId());
    Factory::getAccessGroupUserFactory()->save($accessGroup);
    $this->apiKey = new ApiKey(null, 0, time() + 3600, 'mykey', 0, $this->user->getId(), 1);
    $this->apiKey = Factory::getApiKeyFactory()->save($this->apiKey);
  }

  abstract function run();
  abstract function getTestName();

  protected function testFailed($test, $error){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_ERROR, "Test '$test' failed: $error");
    self::$testCount++;
    self::$status = -1;
  }

  public static function getStatus(){
    return self::$status;
  }
  
  public static function getTestCount(){
    return self::$testCount;
  }

  protected function testSuccess($test){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Test '$test' passed!");
    self::$testCount++;
  }

  public function getMinVersion(){
    return $this->minVersion;
  }

  public function getMaxVersion(){
    return $this->maxVersion;
  }

  public function getRunType(){
    return $this->runType;
  }
}