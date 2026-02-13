<?php

/**
 * @deprecated
 */
class GroupTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType    = HashtopolisTest::RUN_FAST;
  
  public function init($version) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing " . $this->getTestName() . "...");
    parent::init($version);
  }
  
  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    
    $userTest = new UserTest();
    $this->testListGroups([1]);
    $this->testGetGroup(1, [1], []);
    $this->testGetGroup(2, [], [], false);
    $userTest->testCreateUser('testuser2');
    $this->testGetGroup(1, [1, 2], []);
    $this->testCreateGroup('Group #2');
    $this->testListGroups([1, 2]);
    $this->testGetGroup(2, [], []);
    $this->testAddUser(3, 1, false); // invalid groupId
    $this->testAddUser(1, 3, false); // invalid userId
    $this->testAddUser(1, 1, false); // user is already in the group
    $this->testAddUser(2, 2);
    $this->testGetGroup(2, [2], []);
    $this->testRemoveUser(2, 3, false); // invalid userId
    $this->testRemoveUser(3, 2, false); // invalid groupId
    $this->testRemoveUser(1, 2);
    $this->testRemoveUser(1, 2, false); // already removed
    $this->testGetGroup(1, [1], []);
    
    $agentTest = new AgentTest();
    $agentTest->testCreateVoucher('voucher1');
    $agentTest->testAgentRegister(true, 'voucher1');
    $this->testGetGroup(1, [1], [1]);
    $this->testAddAgent(1, 3, false); // invalid agentId
    $this->testAddAgent(3, 1, false); // invalid groupId
    $this->testAddAgent(1, 1, false); // agent already in the group
    $this->testAddAgent(2, 1);
    $this->testGetGroup(2, [2], [1]);
    $this->testRemoveAgent(1, 3, false); // invalid agentId
    $this->testRemoveAgent(3, 1, false); // invalid groupId
    $this->testRemoveAgent(1, 1);
    $this->testRemoveAgent(1, 1, false); // already removed
    $this->testGetGroup(1, [1], []);
    
    $this->testDeleteGroup(3, false); // invalid group
    $this->testDeleteGroup(2);
    $this->testGetGroup(2, [], [], false); // should be deleted now
    
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }
  
  private function testDeleteGroup($groupId, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "group",
      "request" => "deleteGroup",
      "groupId" => $groupId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("GroupTest:testDeleteGroup($groupId,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("GroupTest:testDeleteGroup($groupId,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("GroupTest:testDeleteGroup($groupId,$assert)");
    }
  }
  
  private function testRemoveAgent($groupId, $agentId, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "group",
      "request" => "removeAgent",
      "groupId" => $groupId,
      "agentId" => $agentId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("GroupTest:testRemoveAgent($groupId,$agentId,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("GroupTest:testRemoveAgent($groupId,$agentId,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("GroupTest:testRemoveAgent($groupId,$agentId,$assert)");
    }
  }
  
  private function testAddAgent($groupId, $agentId, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "group",
      "request" => "addAgent",
      "groupId" => $groupId,
      "agentId" => $agentId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("GroupTest:testAddAgent($groupId,$agentId,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("GroupTest:testAddAgent($groupId,$agentId,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("GroupTest:testAddAgent($groupId,$agentId,$assert)");
    }
  }
  
  private function testRemoveUser($groupId, $userId, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "group",
      "request" => "removeUser",
      "groupId" => $groupId,
      "userId" => $userId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("GroupTest:testRemoveUser($groupId,$userId,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("GroupTest:testRemoveUser($groupId,$userId,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("GroupTest:testRemoveUser($groupId,$userId,$assert)");
    }
  }
  
  private function testAddUser($groupId, $userId, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "group",
      "request" => "addUser",
      "groupId" => $groupId,
      "userId" => $userId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("GroupTest:testAddUser($groupId,$userId,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("GroupTest:testAddUser($groupId,$userId,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("GroupTest:testAddUser($groupId,$userId,$assert)");
    }
  }
  
  private function testCreateGroup($name, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "group",
      "request" => "createGroup",
      "name" => $name,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("GroupTest:testCreateGroup($name,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("GroupTest:testCreateGroup($name,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("GroupTest:testCreateGroup($name,$assert)");
    }
  }
  
  private function testGetGroup($groupId, $users, $agents, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "group",
      "request" => "getGroup",
      "groupId" => $groupId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("GroupTest:testGetGroup($groupId,[" . implode(",", $users) . "],[" . implode(",", $agents) . "],$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("GroupTest:testGetGroup($groupId,[" . implode(",", $users) . "],[" . implode(",", $agents) . "],$assert)", "Response does not match assert");
    }
    else {
      if (!$assert) {
        $this->testSuccess("GroupTest:testGetGroup($groupId,[" . implode(",", $users) . "],[" . implode(",", $agents) . "],$assert)");
        return;
      }
      else if (sizeof($users) != sizeof($response['users'])) {
        $this->testFailed("GroupTest:testGetGroup($groupId,[" . implode(",", $users) . "],[" . implode(",", $agents) . "],$assert)", "Response OK, but non matching number of users");
        return;
      }
      else if (sizeof($agents) != sizeof($response['agents'])) {
        $this->testFailed("GroupTest:testGetGroup($groupId,[" . implode(",", $users) . "],[" . implode(",", $agents) . "],$assert)", "Response OK, but non matching number of agents");
        return;
      }
      foreach ($response['users'] as $u) {
        if (!in_array($u, $users)) {
          $this->testFailed("GroupTest:testGetGroup($groupId,[" . implode(",", $users) . "],[" . implode(",", $agents) . "],$assert)", "Response OK, but expected user $u not present");
          return;
        }
      }
      foreach ($response['agents'] as $a) {
        if (!in_array($a, $agents)) {
          $this->testFailed("GroupTest:testGetGroup($groupId,[" . implode(",", $users) . "],[" . implode(",", $agents) . "],$assert)", "Response OK, but expected agent $a not present");
          return;
        }
      }
      $this->testSuccess("GroupTest:testGetGroup($groupId,[" . implode(",", $users) . "],[" . implode(",", $agents) . "],$assert)");
    }
  }
  
  private function testListGroups($groups, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "group",
      "request" => "listGroups",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("GroupTest:testListGroups([" . implode(",", $groups) . "],$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("GroupTest:testListGroups([" . implode(",", $groups) . "],$assert)", "Response does not match assert");
    }
    else {
      if (!$assert) {
        $this->testSuccess("GroupTest:testListGroups([" . implode(",", $groups) . "],$assert)");
        return;
      }
      else if (sizeof($groups) != sizeof($response['groups'])) {
        $this->testFailed("GroupTest:testListGroups([" . implode(",", $groups) . "],$assert)", "Response OK, but non matching number of groups");
        return;
      }
      foreach ($response['groups'] as $g) {
        if (!in_array($g['groupId'], $groups)) {
          $this->testFailed("GroupTest:testListGroups([" . implode(",", $groups) . "],$assert)", "Response OK, but expected group " . $g['groupId'] . " not present");
          return;
        }
      }
      $this->testSuccess("GroupTest:testListGroups([" . implode(",", $groups) . "],$assert)");
    }
  }
  
  public function getTestName() {
    return "Group Test";
  }
}

HashtopolisTestFramework::register(new GroupTest());