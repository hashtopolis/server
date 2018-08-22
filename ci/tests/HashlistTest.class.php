<?php

class HashlistTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType = HashtopolisTest::RUN_FAST;

  private $token = "";

  public function init($version){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing ".$this->getTestName()."...");
    parent::init($version);
  }

  public function run(){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running ".$this->getTestName()."...");
    $this->testListHashlists();
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName()." completed");
  }

  private function testListHashlists($assert = []){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "hashlist",
      "request" => "listHashlists",
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("HashlistTest:testListHashlists(" . implode(", ", $assert) . ")", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("HashlistTest:testListHashlists(" . implode(", ", $assert) . ")", "Response not OK");
    }
    else{
      $this->testSuccess("HashlistTest:testListHashlists(" . implode(", ", $assert) . ")");
    }
  }

  public function getTestName(){
    return "Hashlist Test";
  }
}

HashtopolisTestFramework::register(new HashlistTest());