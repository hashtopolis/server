<?php

class AccountTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType    = HashtopolisTest::RUN_FAST;
  
  public function init($version) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing " . $this->getTestName() . "...");
    parent::init($version);
  }
  
  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    $this->testGetInformation(["userId" => 1, "rightGroupId" => 1]);
    $this->testSetEmail('otheremail@example.org');
    $this->testGetInformation(["userId" => 1, "rightGroupId" => 1, 'email' => 'otheremail@example.org']);
    $this->testSetEmail('invalid-email', false);
    $this->testSetEmail('', false);
    $this->testGetInformation(["userId" => 1, "rightGroupId" => 1, 'email' => 'otheremail@example.org']);
    $this->testSetSessionLength(6000);
    $this->testSetSessionLength(500000, false);
    $this->testSetSessionLength(0, false);
    $this->testSetSessionLength(-6000, false);
    $this->testGetInformation(["userId" => 1, "rightGroupId" => 1, 'email' => 'otheremail@example.org', 'sessionLength' => 6000]);
    $this->testChangePassword(HashtopolisTest::USER_PASS, 'newPassword');
    $this->testChangePassword(HashtopolisTest::USER_PASS, 'newPassword', false);
    $this->testChangePassword('newPassword', 'newPassword', false);
    $this->testChangePassword('newPassword', '', false);
    $this->testChangePassword('newPassword', '123', false);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }
  
  private function testChangePassword($old, $new, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "account",
      "request" => "changePassword",
      "oldPassword" => $old,
      "newPassword" => $new,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("AccountTest:testChangePassword($old,$new,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("AccountTest:testChangePassword($old,$new,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("AccountTest:testChangePassword($old,$new,$assert)");
    }
  }
  
  private function testSetSessionLength($length, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "account",
      "request" => "setSessionLength",
      "sessionLength" => $length,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("AccountTest:testSetSessionLength($length,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("AccountTest:testSetSessionLength($length,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("AccountTest:testSetSessionLength($length,$assert)");
    }
  }
  
  private function testSetEmail($email, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "account",
      "request" => "setEmail",
      "email" => $email,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("AccountTest:testSetEmail($email,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("AccountTest:testSetEmail($email,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("AccountTest:testSetEmail($email,$assert)");
    }
  }
  
  private function testGetInformation($data, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "account",
      "request" => "getInformation",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("AccountTest:testGetInformation([" . implode(", ", $data) . "],$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("AccountTest:testGetInformation([" . implode(", ", $data) . "],$assert)", "Response does not match assert");
    }
    else {
      if (!$assert) {
        $this->testSuccess("AccountTest:testGetInformation([" . implode(", ", $data) . "],$assert)");
        return;
      }
      foreach ($data as $key => $val) {
        if (!isset($response[$key]) || $val != $response[$key]) {
          $this->testFailed("AccountTest:testGetInformation([" . implode(", ", $data) . "],$assert)", "Response OK, but wrong response");
          return;
        }
      }
      $this->testSuccess("AccountTest:testGetInformation([" . implode(", ", $data) . "],$assert)");
    }
  }
  
  public function getTestName() {
    return "Account Test";
  }
}

HashtopolisTestFramework::register(new AccountTest());