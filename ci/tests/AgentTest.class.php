<?php

class AgentTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType = HashtopolisTest::RUN_FAST;

  public function init($version){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing ".$this->getTestName()."...");
    parent::init($version);
  }

  public function run(){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running ".$this->getTestName()."...");
    $this->testListVouchers();
    $this->testCreateVoucher();
    $this->testCreateVoucher('othervoucher');
    $this->testCreateVoucher('myvoucher', false);
    $this->testListVouchers(['myvoucher', 'othervoucher']);
    $this->testDeleteVoucher();
    $this->testListVouchers(['myvoucher']);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName()." completed");
  }

  private function testDeleteVoucher($voucher = 'othervoucher'){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "deleteVoucher",
      "voucher" => $voucher,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("AgentTest:testCreateVoucher($voucher)", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("AgentTest:testCreateVoucher($voucher)", "Response not OK");
    }
    else{
      $this->testSuccess("AgentTest:testCreateVoucher($voucher)");
    }
  }

  private function testListVouchers($assert = []){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "listVouchers",
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("AgentTest:testCreateVoucher(" . implode(",", $assert) . ")", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("AgentTest:testCreateVoucher(" . implode(",", $assert) . ")", "Response not OK");
    }
    else if(sizeof($response['vouchers']) != sizeof($assert)){
      $this->testFailed("AgentTest:testCreateVoucher(" . implode(",", $assert) . ")", "Number of vouchers does not match");
    }
    else{
      foreach($response['vouchers'] as $vouch){
        if(!in_array($vouch, $assert)){
          $this->testFailed("AgentTest:testCreateVoucher(" . implode(",", $assert) . ")", "$vouch in response but not in assert");
          return;
        }
      }
      $this->testSuccess("AgentTest:testCreateVoucher(" . implode(",", $assert) . ")");
    }
  }

  private function testCreateVoucher($voucher = 'myvoucher', $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "createVoucher",
      "voucher" => $voucher,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("AgentTest:testCreateVoucher($voucher,$assert)", "Empty response");
    }
    else if($assert && $response['response'] != 'OK'){
      $this->testFailed("AgentTest:testCreateVoucher($voucher,$assert)", "Response not OK");
    }
    else if(!$assert && $response['response'] != 'ERROR'){
      $this->testFailed("AgentTest:testCreateVoucher($voucher,$assert)", "Response not ERROR");
    }
    else{
      $this->testSuccess("AgentTest:testCreateVoucher($voucher,$assert)");
    }
  }

  public function getTestName(){
    return "Agent Test";
  }
}

HashtopolisTestFramework::register(new AgentTest());