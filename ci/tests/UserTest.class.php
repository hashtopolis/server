<?php

class UserTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType = HashtopolisTest::RUN_FAST;

  public function init($version){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing ".$this->getTestName()."...");
    parent::init($version);
  }

  public function run(){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running ".$this->getTestName()."...");
    $this->testListUsers([]);
    $this->testCreateUser('testuser');
    $this->testListUsers(['testuser']);
    $this->testCreateUser('testuser', false);
    $this->testCreateUser('user2');
    $this->testListUsers(['user2', 'testuser']);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName()." completed");
  }

  private function testCreateUser($username, $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "user",
      "request" => "createUser",
      "username" => $username,
      "email" => $username . "@example.org",
      "rightGroupId" => 1,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("UserTest:testCreateUser($username, $assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("UserTest:testCreateUser($username, $assert)", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("UserTest:testCreateUser($username, $assert)", "Response OK, but expected to fail");
      }
      $this->testSuccess("UserTest:testCreateUser($username, $assert)");
    }
  }

  private function testListUsers($assert){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "user",
      "request" => "listUsers",
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("UserTest:testListUsers(" . implode(", ", $assert) . ")", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("UserTest:testListUsers(" . implode(", ", $assert) . ")", "Response not OK");
    }
    else if(sizeof($assert) != sizeof($response['tasks'])){
      $this->testFailed("UserTest:testListUsers(" . implode(", ", $assert) . ")", "Not matching number of users");
    }
    else{
      foreach($response['users'] as $user){
        if(!in_array($user['username'], $assert)){
          $this->testFailed("UserTest:testListUsers(" . implode(", ", $assert) . ")", "Not matching username");
          return;
        }
      }
      $this->testSuccess("UserTest:testListUsers(" . implode(", ", $assert) . ")");
    }
  }

  public function getTestName(){
    return "User Test";
  }
}

HashtopolisTestFramework::register(new UserTest());