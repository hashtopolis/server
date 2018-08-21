<?php
use DBA\Factory;
use DBA\User;
use DBA\ApiKey;

abstract class HashtopolisTest{
  protected $minVersion;
  protected $maxVersion;
  protected $runType;

  protected static $status = 0;

  protected $user;
  protected $apiKey;

  const RUN_FULL = 0;
  const RUN_FAST = 1;

  public function init($version){
    // drop old data and create empty DB
    Factory::getAgentFactory()->getDB()->query("DROP DATABASE IF EXISTS hashtopolis");
    Factory::getAgentFactory()->getDB()->query("CREATE DATABASE hashtopolis");
    Factory::getAgentFactory()->getDB()->query("USE hashtopolis");

    sleep(1);

    // load DB
    Factory::getAgentFactory()->getDB()->query(file_get_contents(dirname(__FILE__)."/files/db_".$version.".sql"));

    // insert user and api key
    $this->user = new User(0, 'testuser', '', '', '', 1, 0, 0, 0, 3600, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), 0, '', '', '', '');
    $this->user = Factory::getUserFactory()->save($this->user);
    $this->apiKey = new ApiKey(0, 0, time() + 3600, 'mykey', 0, $this->user->getId(), 1);
    $this->apiKey = Factory::getApiKeyFactory()->save($this->apiKey);
  }

  abstract function run();
  abstract function getTestName();

  protected function testFailed($test, $error){
    echo "ERROR:  Test '$test' failed: $error\n";
    self::$status = -1;
  }

  public static function getStatus(){
    return self::$status;
  }

  protected function testSuccess($test){
    echo "SUCCESS: Test '$test' passed!\n";
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