<?php

// TODO: do necessary includes
require_once(dirname(__FILE__)."/../src/inc/Util.class.php");

class HashtopolisTestFramework{
  const REQUEST_CLIENT = 0;
  const REQUEST_UAPI   = 1;

  /**
   * @var $instance HashtopolisTest[]
   */
  private static $instances = [];

  public static function register($test){
    self::$instances[] = $test;
  }

  public function __construct(){
    // Test if environment is ready -> test connection api call
  }

  /**
   * @param string $version 
   * @param int $runType 
   */
  public function execute($version, $runType){
    foreach(self::$instances as $instance){
      if(Util::versionComparison($version, $instance->getMinVersion() > 0)){
        echo "Ignoring ".$instance->getTestName().": minimum ".$instance->getMinVersion()." required, but testing $version...\n";
        continue;
      }
      else if(Util::versionComparison($version, $instance->getMaxVersion() < 0)){
        echo "Ignoring ".$instance->getTestName().": maximum ".$instance->getMaxVersion()." required, but testing $version...\n";
        continue;
      }
      else if($runType > $instance->getRunType()){
        continue;
      }
      $instance->init($version);
      $instance->run();
    }
  }

  /**
   * @param array $request 
   * @param int $requestType 
   * @return array
   */
  public static function doRequest($request, $requestType = HashtopolisTestFramework::REQUEST_CLIENT){
    switch($requestType){
      case HashtopolisTestFramework::REQUEST_CLIENT:
        $ch = curl_init('http://localhost/api/server.php');
        break;
      case HashtopolisTestFramework::REQUEST_UAPI:
        $ch = curl_init('http://localhost/api/user.php');
        break;
      default:
        return false;
    }
    curl_setopt_array($ch, array(
        CURLOPT_POST => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_POSTFIELDS => json_encode($request)
    ));
    $response = curl_exec($ch);
    if($response === FALSE){
      echo "ERROR: Request failed!\n";
      return false;
    }
    return json_decode($response, TRUE);
  }
}