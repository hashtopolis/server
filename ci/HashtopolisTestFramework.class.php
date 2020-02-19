<?php

class HashtopolisTestFramework {
  const REQUEST_CLIENT = 0;
  const REQUEST_UAPI   = 1;
  
  /**
   * @var $instance HashtopolisTest[]
   */
  private static $instances = [];
  public static  $logLevel  = HashtopolisTestFramework::LOG_INFO;
  
  public static function register($test) {
    self::$instances[] = $test;
  }
  
  public function __construct() {
    // Test if environment is ready -> test connection api call
  }
  
  /**
   * @param string $version
   * @param int $runType
   * @return int
   */
  public function execute($version, $runType) {
    foreach (self::$instances as $instance) {
      if ($version != 'master' && (Util::versionComparison($version, $instance->getMinVersion()) > 0 || $instance->getMinVersion() == 'master')) {
        echo "Ignoring " . $instance->getTestName() . ": minimum " . $instance->getMinVersion() . " required, but testing $version...\n";
        continue;
      }
      else if ($instance->getMaxVersion() != 'master' && (Util::versionComparison($version, $instance->getMaxVersion()) < 0 || $version == 'master')) {
        echo "Ignoring " . $instance->getTestName() . ": maximum " . $instance->getMaxVersion() . " required, but testing $version...\n";
        continue;
      }
      else if ($runType > $instance->getRunType()) {
        continue;
      }
      $instance->init($version);
      $instance->run();
    }
    return HashtopolisTest::getStatus();
  }
  
  public function executeWithUpgrade($fromVersion, $runType) {
    foreach (self::$instances as $instance) {
      if ($instance->getMaxVersion() != 'master') {
        echo "Ignoring " . $instance->getTestName() . ": maximum " . $instance->getMaxVersion() . " required, but testing master...\n";
        continue;
      }
      else if ($runType > $instance->getRunType()) {
        continue;
      }
      $instance->initAndUpgrade($fromVersion);
      $instance->run();
    }
    return HashtopolisTest::getStatus();
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
}