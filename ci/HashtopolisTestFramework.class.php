<?php

use DBA\Factory;

class HashtopolisTestFramework {
  const REQUEST_CLIENT = 0;
  const REQUEST_UAPI   = 1;
  
  /**
   * @var $instance HashtopolisTest[]
   */
  private static $instances = [];
  public static  $logLevel  = HashtopolisTestFramework::LOG_INFO;

  private $dbBackupFile;

  public static function register($test) {
    self::$instances[] = $test;
  }
  
  public function __construct() {
    // Test if environment is ready -> test connection api call
  }
  
  /**
   * @param string $version
   * @param string [] $testNames
   * @param int $runType
   * @return int
   */
  public function execute($version, $testNames, $runType) {
    try {
      $this->backupDatabase();

      foreach (self::$instances as $instance) {
        if ($this-> isTestIncluded($instance, $version, $testNames, $runType, false)) {
          $instance->init($version);
          $instance->run();
        }
      }
    }
    finally {
      $this->restoreDatabase();
    }
    return HashtopolisTest::getStatus();
 }

  public function executeWithUpgrade($fromVersion, $testNames, $runType) {
    try {
      $this->backupDatabase();

      foreach (self::$instances as $instance) {
        if ($this-> isTestIncluded($instance, $fromVersion, $testNames, $runType, true)) {
          $instance->initAndUpgrade($fromVersion);
          $instance->run();
        }
      }
    }
    finally {
      $this->restoreDatabase();
    }
    return HashtopolisTest::getStatus();
}

private function backupDatabase() {
  global $CONN;

  if (!file_exists(dirname(__FILE__) . "/../ci/db-backups")) {
    mkdir(dirname(__FILE__) . "/../ci/db-backups");
  }

  $this->dbBackupFile = dirname(__FILE__) . "/../ci/db-backups/database_backup_" . date('Y_m_d-H_i_s').  ".sql";
  HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Backup database to " . $this->dbBackupFile . "...");

  // Note that the '-y' option avoids requirement on 'PROCESS' privilege for the 'hashtopolis' user!
  exec("mysqldump hashtopolis -y -h".$CONN['server'] . " -P".$CONN['port'] . " -u".$CONN['user'] . " -p".$CONN['pass'] ." --skip-ssl > " . $this->dbBackupFile, $output, $status);
  if ($status != 0) {
    $this->dbBackupFile = "";

    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_ERROR, "Database backup failed.");
    die("test aborted!\n");
  }
}

private function restoreDatabase() {
  global $CONN;

  if (!empty($this->dbBackupFile)) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Restoring database from " . $this->dbBackupFile . "...");

    // drop old data
    Factory::getAgentFactory()->getDB()->query("DROP DATABASE IF EXISTS hashtopolis");
    Factory::getAgentFactory()->getDB()->query("CREATE DATABASE hashtopolis");
    Factory::getAgentFactory()->getDB()->query("USE hashtopolis");

    // restore original DB
    Factory::getAgentFactory()->getDB()->query(file_get_contents($this->dbBackupFile));

    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Database successfully restored!" . $this->dbBackupFile . "...");
  }
}

private function isTestIncluded($instance, $version, $testNames, $runType, $upgrade) {
  if (!empty($testNames) && !in_array(get_class($instance), $testNames)) {
    return false;
  }
  if (!$upgrade && $version != 'master' && (Util::versionComparison($version, $instance->getMinVersion()) > 0 || $instance->getMinVersion() == 'master')) {
    echo "Ignoring " . $instance->getTestName() . ": minimum " . $instance->getMinVersion() . " required, but testing $version...\n";
    return false;
  }
  if ($instance->getMaxVersion() != 'master' && (Util::versionComparison($version, $instance->getMaxVersion()) < 0 || $version == 'master')) { 
    echo "Ignoring " . $instance->getTestName() . ": maximum " . $instance->getMaxVersion() . " required, but testing $version...\n";
    return false;
  }
  if ($runType > $instance->getRunType()) {
    return false;
  }

  return true;
}

  /**
   * @param array $request
   * @param int $requestType
   * @return array|bool
   */
  public static function doRequest($request, $requestType = HashtopolisTestFramework::REQUEST_CLIENT) {
    switch ($requestType) {
      case HashtopolisTestFramework::REQUEST_CLIENT:
        $url = 'http://localhost/api/server.php';
        break;
      case HashtopolisTestFramework::REQUEST_UAPI:
        $url = 'http://localhost/api/user.php';
        break;
      default:
        return false;
    }
    $ch = curl_init($url);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_DEBUG, $url . " <- " . substr(json_encode($request), 0, 500));
    curl_setopt_array($ch, array(
        CURLOPT_POST => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_POSTFIELDS => json_encode($request)
      )
    );
    $response = curl_exec($ch);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_DEBUG, $url . " -> " . $response);
    if ($response === FALSE) {
      echo "ERROR: Request failed!\n";
      return false;
    }
    return json_decode($response, TRUE);
  }
  
  const LOG_DEBUG = 0;
  const LOG_INFO  = 1;
  const LOG_ERROR = 2;
  
  public static function log($level, $message) {
    if ($level < HashtopolisTestFramework::$logLevel) {
      return;
    }
    switch ($level) {
      case HashtopolisTestFramework::LOG_DEBUG:
        $lvl = "DEBUG";
        break;
      case HashtopolisTestFramework::LOG_INFO:
        $lvl = "INFO ";
        break;
      case HashtopolisTestFramework::LOG_ERROR:
        $lvl = "ERROR";
        break;
      default:
        $lvl = "FFFFF";
        break;
    }
    echo "[" . date("d.m.Y - H:i:s") . "][" . $lvl . "]: " . $message . "\n";
  }

  public static function reportTestSummary() { 
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, " --Test Report-- ");
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Total Test Count : " . HashtopolisTest::getTestCount());
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Tests Passed : " . HashtopolisTest::getTestsPassedCount());
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Tests Failed : " . HashtopolisTest::getTestsFailedCount());
  
    $failedTests = HashtopolisTest::getFailedTests();
    if (!empty($failedTests)) {  
      HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Failed Tests:");
      foreach ($failedTests as &$value) {
        HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, " " . $value);
      }
    }
  }
}
