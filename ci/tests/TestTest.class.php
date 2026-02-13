<?php

/**
 * @deprecated
 */
class TestTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType    = HashtopolisTest::RUN_FAST;
  
  public function init($version) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing " . $this->getTestName() . "...");
    parent::init($version);
  }
  
  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    $this->testConnection();
    $this->testApiKey();
    $this->testApiKey('invalidKey', false);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }
  
  private function testApiKey($key = 'mykey', $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "test",
      "request" => "access",
      "accessKey" => $key
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("TestTest:testApiKey($key,$assert)", "Empty response");
    }
    else if ($assert && $response['response'] != 'OK') {
      $this->testFailed("TestTest:testApiKey($key,$assert)", "Response not OK");
    }
    else if (!$assert && $response['response'] != 'ERROR') {
      $this->testFailed("TestTest:testApiKey($key,$assert)", "Response not ERROR");
    }
    else {
      $this->testSuccess("TestTest:testApiKey($key,$assert)");
    }
  }
  
  private function testConnection() {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "test",
      "request" => "connection"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("TestTest:testConnection", "Empty response");
    }
    else if ($response['response'] != 'SUCCESS') {
      $this->testFailed("TestTest:testConnection", "Response not SUCCESS");
    }
    else {
      $this->testSuccess("TestTest:testConnection");
    }
  }
  
  public function getTestName() {
    return "Test Test";
  }
}

HashtopolisTestFramework::register(new TestTest());