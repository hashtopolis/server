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
    $this->testGetUser(2, ['username' => 'testuser', 'userId' => 2, 'email' => 'testuser@example.org', 'rightGroupId' => 1, 'isValid' => true]);
    $this->testGetUser(3, ['username' => 'user2', 'userId' => 3, 'email' => 'user2@example.org', 'rightGroupId' => 1, 'isValid' => true]);
    $this->testGetUser(4, [], false);
    $this->testDisableUser(1, false);
    $this->testDisableUser(1234, false);
    $this->testDisableUser(2);
    $this->testGetUser(2, ['username' => 'testuser', 'userId' => 2, 'email' => 'testuser@example.org', 'rightGroupId' => 1, 'isValid' => false]);
    $this->testEnableUser(2);
    $this->testEnableUser(1234, false);
    $this->testGetUser(2, ['username' => 'testuser', 'userId' => 2, 'email' => 'testuser@example.org', 'rightGroupId' => 1, 'isValid' => true]);
    $this->testSetPassword(2, true);
    $this->testSetPassword(1, false);
    $this->testSetPassword(1234, false);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName()." completed");
  }

  private function testSetPassword($userId, $assert){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "user",
      "request" => "setUserPassword",
      "userId" => $userId,
      "password" => Util::randomString(20),
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("UserTest:testSetPassword($userId,$assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("UserTest:testSetPassword($userId,$assert", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("UserTest:testSetPassword($userId,$assert", "Response OK, but expected to fail");
      }
      $this->testSuccess("UserTest:testSetPassword($userId,$assert");
    }
  }

  private function testEnableUser($userId, $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "user",
      "request" => "enableUser",
      "userId" => $userId,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("UserTest:testEnableUser($userId,$assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("UserTest:testEnableUser($userId,$assert", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("UserTest:testEnableUser($userId,$assert", "Response OK, but expected to fail");
      }
      $this->testSuccess("UserTest:testEnableUser($userId,$assert");
    }
  }

  private function testDisableUser($userId, $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "user",
      "request" => "disableUser",
      "userId" => $userId,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("UserTest:testDisableUser($userId,$assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("UserTest:testDisableUser($userId,$assert", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("UserTest:testDisableUser($userId,$assert", "Response OK, but expected to fail");
      }
      $this->testSuccess("UserTest:testDisableUser($userId,$assert");
    }
  }

  private function testGetUser($userId, $data, $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "user",
      "request" => "getUser",
      "userId" => $userId,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("UserTest:testGetUser($userId," . implode(", ", $data) . ",$assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("UserTest:testGetUser($userId," . implode(", ", $data) . "),$assert", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("UserTest:testGetUser($userId," . implode(", ", $data) . "),$assert", "Response OK, but expected to fail");
      }
      foreach($data as $key => $val){
        if(!isset($response[$key]) || $val != $response[$key]){
          $this->testFailed("UserTest:testGetUser($userId," . implode(", ", $data) . "),$assert", "Not all data present or matching");
          return;
        }
      }
      $this->testSuccess("UserTest:testGetUser($userId," . implode(", ", $data) . "),$assert");
    }
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