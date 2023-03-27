<?php

use DBA\Factory;
use DBA\User;
use DBA\ApiKey;
use DBA\AccessGroupUser;
use DBA\StoredValue;

abstract class HashtopolisTest {
  protected $minVersion;
  protected $maxVersion;
  protected $runType;
  
  protected static $status    = 0;
  protected static $testCount = 0;
 
  protected static $failed = array();

  protected $user;
  protected $apiKey;
  
  const USER_PASS = "HG78Ghdfs87gh";
  
  const RUN_FULL = 0;
  const RUN_FAST = 1;
  
  
  protected $RELEASES = [
    "0.8.0" => "47e4444c22cbfae08f8e8f974fb6ca6bfa0e944d",
    "0.9.0" => "cd2951cd10552114c44c29962ac22efcbabf57c7",
    "0.10.0" => "cdc674f4f375115debd556feda4e7f6e4614a2c6",
    "0.11.0" => "9cdbffcffb46da613c14d2f46266c1c3672e61e7",
    "0.12.0" => "b53f529f664c866e4d22f5cb348d22eb6f542901",
    "0.13.0" => "c7036800be5b2e21542df0fa9bd4d19ebf7ecdf3"
  ];
  
  public function initAndUpgrade($fromVersion) {
    // these global variables are needed in the included update.php script
    global $VERSION, $BUILD, $TEST;
    
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initialize old version $fromVersion...");
    $this->init($fromVersion);
    
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running upgrades...");
    include(dirname(__FILE__) ."/../src/install/updates/update.php");
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initialization with upgrade done!");
  }
  
  public static function multiImplode($glue, $array) {
    $output = "";
    foreach ($array as $item) {
      if (is_array($item)) {
        $output .= HashtopolisTest::multiImplode($glue, $item) . $glue;
      }
      else {
        $output .= $item . $glue;
      }
    }
    $ret = substr($output, 0, 0 - strlen($glue));
    return $ret;
  }
  
  public function init($version) {
    global $PEPPER, $VERSION;
    
    // drop old data and create empty DB
    Factory::getAgentFactory()->getDB()->query("DROP DATABASE IF EXISTS hashtopolis");
    Factory::getAgentFactory()->getDB()->query("CREATE DATABASE hashtopolis");
    Factory::getAgentFactory()->getDB()->query("USE hashtopolis");
    
    // load DB
    if ($version == "master") {
      Factory::getAgentFactory()->getDB()->query(file_get_contents(dirname(__FILE__) ."/../src/install/hashtopolis.sql"));
    }
    else {
      Factory::getAgentFactory()->getDB()->query(file_get_contents(dirname(__FILE__) . "/files/db_" . $version . ".sql"));
    }
    
    sleep(1);
    
    // insert user and api key
    $salt = Util::randomString(30);
    $hash = Encryption::passwordHash(HashtopolisTest::USER_PASS, $salt);
    $this->user = new User(null, 'testuser', '', $hash, $salt, 1, 0, 0, 0, 3600, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), 0, '', '', '', '');
    $this->user = Factory::getUserFactory()->save($this->user);
    $accessGroup = new AccessGroupUser(null, 1, $this->user->getId());
    Factory::getAccessGroupUserFactory()->save($accessGroup);
    $this->apiKey = new ApiKey(null, 0, time() + 3600, 'mykey', 0, $this->user->getId(), 1);
    $this->apiKey = Factory::getApiKeyFactory()->save($this->apiKey);
    // $versionStore = new StoredValue("version", ($version == 'master') ? explode("+", $VERSION)[0] : $version);
    // Factory::getStoredValueFactory()->save($versionStore);
    // $buildStore = new StoredValue("build", ($version == 'master') ? Util::getGitCommit(true) : $this->RELEASES[$version]);
    // Factory::getStoredValueFactory()->save($buildStore);
  }
  
  abstract function run();
  
  abstract function getTestName();
  
  protected function testFailed($test, $error) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_ERROR, "Test '$test' failed: $error");
    self::$testCount++;
    self::$status = -1;

    self::$failed[] = $test; 
  }
  
  public function validState($response, $assert) {
    if ($response == 'OK' && $assert) {
      return true;
    }
    else if ($response == 'ERROR' && !$assert) {
      return true;
    }
    return false;
  }
  
  public static function getStatus() {
    return self::$status;
  }
  
  public static function getTestCount() {
    return self::$testCount;
  }

  public static function getTestsPassedCount() {
    return self::$testCount - count(self::$failed);
  }
  
  public static function getTestsFailedCount() {
    return count(self::$failed);
  }

  public static function getFailedTests() {
    return self::$failed;
  }
 
  protected function testSuccess($test) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Test '$test' passed!");
    self::$testCount++;
  }
  
  public function getMinVersion() {
    return $this->minVersion;
  }
  
  public function getMaxVersion() {
    return $this->maxVersion;
  }
  
  public function getRunType() {
    return $this->runType;
  }

  protected function deleteTaskIfExists($name){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "listTasks",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );

    foreach ($response["tasks"] as $task) {
      if ($task["name"] == $name) {
        if ($task['type'] == 1) {
          $response = HashtopolisTestFramework::doRequest([
            "section" => "task",
            "request" => "deleteSupertask",
            "supertaskId" => $task['supertaskId'],
            "accessKey" => "mykey"
            ], HashtopolisTestFramework::REQUEST_UAPI
          );
        } else {
          $response = HashtopolisTestFramework::doRequest([
            "section" => "task",
            "request" => "deleteTask",
            "taskId" => $task["taskId"],
            "accessKey" => "mykey"
            ], HashtopolisTestFramework::REQUEST_UAPI
          );
        }
        if ($response === false) {
          return false;
        }
      }
    }
    return true;
  }

  protected function deleteHashlistIfExists($name){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "hashlist",
      "request" => "listHashlists",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );

    foreach ($response["hashlists"] as $hashlist) {
      if ($hashlist["name"] == $name) {
        $response = HashtopolisTestFramework::doRequest([
          "section" => "hashlist",
          "request" => "deleteHashlist",
          "hashlistId" => $hashlist["hashlistId"],
          "accessKey" => "mykey"
          ], HashtopolisTestFramework::REQUEST_UAPI
          );
          if ($response === false) {
            return false;
          }
        }
    }
    return true;
  }

  protected function deleteFileIfExists($name) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "listFiles",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );

    foreach ($response["files"] as $file) {
      if ($file["filename"] == $name) {
        $response = HashtopolisTestFramework::doRequest([
          "section" => "file",
          "request" => "deleteFile",
          "fileId" => $file["fileId"],
          "accessKey" => "mykey"
        ], HashtopolisTestFramework::REQUEST_UAPI
        );
        if ($response === false) {
          return false;
        }
      }
    }
    return true;
  }
}