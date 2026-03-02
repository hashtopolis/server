<?php

/**
 * @deprecated
 */
class AgentTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType    = HashtopolisTest::RUN_FAST;
  
  private $token = "";
  
  public function init($version) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing " . $this->getTestName() . "...");
    parent::init($version);
  }
  
  public function getToken() {
    return $this->token;
  }
  
  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    // voucher section
    $this->testListVouchers();
    $this->testCreateVoucher();
    $this->testCreateVoucher('othervoucher');
    $this->testCreateVoucher('myvoucher', false);
    $this->testListVouchers(['myvoucher', 'othervoucher']);
    $this->testDeleteVoucher();
    $this->testListVouchers(['myvoucher']);
    // agent section
    $this->testListAgents();
    $this->testAgentRegister();
    $this->testAgentRegister(false);
    $this->testListAgents(['Test Agent']);
    $this->testGetAgent(1, ['name' => 'Test Agent', 'owner' => ['userId' => 0, 'username' => '-'], 'isTrusted' => false, 'isCpuOnly' => false]);
    $this->testDeleteAgent(1);
    $this->testListAgents();
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }
  
  public function testDeleteAgent($agentId) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "deleteAgent",
      "agentId" => $agentId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("AgentTest:testDeleteAgent($agentId)", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("AgentTest:testDeleteAgent($agentId)", "Response not OK");
    }
    else {
      $this->testSuccess("AgentTest:testDeleteAgent($agentId)");
    }
  }
  
  public function testGetAgent($agentId, $assert = []) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "get",
      "agentId" => $agentId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("AgentTest:testGetAgent($agentId," . json_encode($assert) . ")", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("AgentTest:testGetAgent($agentId," . json_encode($assert) . ")", "Response not OK");
    }
    else {
      if ($this->checkAssert($agentId, $response, $assert) === false) {
        return;
      }
      $this->testSuccess("AgentTest:testGetAgent($agentId," . json_encode($assert) . ")");
    }
  }
  
  public function checkAssert($agentId, $response, $assert) {
    foreach ($assert as $key => $value) {
      if (!isset($response[$key])) {
        $this->testFailed("AgentTest:testGetAgent($agentId," . json_encode($assert) . ")", "Key ($key) from assert not present in response");
        return false;
      }
      else if (is_array($value)) {
        $status = $this->checkAssert($agentId, $response[$key], $value);
        if ($status === false) {
          return false;
        }
      }
      else if ($response[$key] != $value) {
        $this->testFailed("AgentTest:testGetAgent($agentId," . json_encode($assert) . ")", "Value ($key,$value) from assert does not match response");
        return false;
      }
    }
    return true;
  }
  
  public function testAgentRegister($assert = true, $voucher = 'myvoucher') {
    $response = HashtopolisTestFramework::doRequest([
      "action" => "register",
      "voucher" => $voucher,
      "name" => "Test Agent"
    ], HashtopolisTestFramework::REQUEST_CLIENT
    );
    if ($response === false) {
      $this->testFailed("AgentTest:testAgentRegister($assert)", "Empty response");
    }
    else if ($assert && $response['response'] != 'SUCCESS') {
      $this->testFailed("AgentTest:testAgentRegister($assert)", "Response not OK");
    }
    else if (!$assert && $response['response'] != 'ERROR') {
      $this->testFailed("AgentTest:testAgentRegister($assert)", "Response not ERROR");
    }
    else {
      if ($assert) {
        $this->token = $response['token']; // save for later tests
      }
      $this->testSuccess("AgentTest:testAgentRegister($assert)");
    }
  }
  
  public function testListAgents($assert = []) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "listAgents",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("AgentTest:testListAgents(" . implode(",", $assert) . ")", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("AgentTest:testListAgents(" . implode(",", $assert) . ")", "Response not OK");
    }
    else if (sizeof($response['agents']) != sizeof($assert)) {
      $this->testFailed("AgentTest:testListAgents(" . implode(",", $assert) . ")", "Number of agents does not match");
    }
    else {
      foreach ($response['agents'] as $agent) {
        if (!in_array($agent['name'], $assert)) {
          $this->testFailed("AgentTest:testListAgents(" . implode(",", $assert) . ")", $agent['name'] . " in response but not in assert");
          return;
        }
      }
      $this->testSuccess("AgentTest:testListAgents(" . implode(",", $assert) . ")");
    }
  }
  
  public function testDeleteVoucher($voucher = 'othervoucher') {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "deleteVoucher",
      "voucher" => $voucher,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("AgentTest:testCreateVoucher($voucher)", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("AgentTest:testCreateVoucher($voucher)", "Response not OK");
    }
    else {
      $this->testSuccess("AgentTest:testCreateVoucher($voucher)");
    }
  }
  
  public function testListVouchers($assert = []) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "listVouchers",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("AgentTest:testCreateVoucher(" . implode(",", $assert) . ")", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("AgentTest:testCreateVoucher(" . implode(",", $assert) . ")", "Response not OK");
    }
    else if (sizeof($response['vouchers']) != sizeof($assert)) {
      $this->testFailed("AgentTest:testCreateVoucher(" . implode(",", $assert) . ")", "Number of vouchers does not match");
    }
    else {
      foreach ($response['vouchers'] as $vouch) {
        if (!in_array($vouch, $assert)) {
          $this->testFailed("AgentTest:testCreateVoucher(" . implode(",", $assert) . ")", "$vouch in response but not in assert");
          return;
        }
      }
      $this->testSuccess("AgentTest:testCreateVoucher(" . implode(",", $assert) . ")");
    }
  }
  
  public function testCreateVoucher($voucher = 'myvoucher', $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "createVoucher",
      "voucher" => $voucher,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("AgentTest:testCreateVoucher($voucher,$assert)", "Empty response");
    }
    else if ($assert && $response['response'] != 'OK') {
      $this->testFailed("AgentTest:testCreateVoucher($voucher,$assert)", "Response not OK");
    }
    else if (!$assert && $response['response'] != 'ERROR') {
      $this->testFailed("AgentTest:testCreateVoucher($voucher,$assert)", "Response not ERROR");
    }
    else {
      $this->testSuccess("AgentTest:testCreateVoucher($voucher,$assert)");
    }
  }
  
  public function getTestName() {
    return "Agent Test";
  }
}

HashtopolisTestFramework::register(new AgentTest());