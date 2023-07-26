<?php

class MaxAgentsTest extends HashtopolisTest {
  protected $minVersion = "0.12.0";
  protected $maxVersion = "master";
  protected $runType    = HashtopolisTest::RUN_FAST;

  public function init($version) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing " . $this->getTestName() . "...");
    parent::init($version);
  }

  private function prepare() {
    $status = true;
    // add some files
    $status &= $this->addFile("example.dict", 0);
    $status &= $this->addFile("best64.rule", 1);

    if (!$status) {
      HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_ERROR, "Some initialization failed, most likely tests will fail!");
    }
  }

  private function cleanup() {
    $status = true;
    // delete the created tasks
    $status &= $this->deleteTaskIfExists("task-1");
    $status &= $this->deleteTaskIfExists("task-2");

    // remove the added files
    $status &= $this->deleteFileIfExists("example.dict");
    $status &= $this->deleteFileIfExists("best64.rule");

    if (!$status) {
      HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_ERROR, "Some cleanup failed, deleting task or deleting files not succesful!");
    }
  }

  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    $this->prepare();
    try {
      $this->testMaxAgents();
    }
    finally {
      $this->cleanup();
    }
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }

  private function testMaxAgents() {
    $response = $this->addHashlist(["name" => "NotSecureList", "isSecure" => false]);
    $hashlistId = $response["hashlistId"];

    $agent1 = $this->createAgent("agent-1");
    $agent2 = $this->createAgent("agent-2");

    // register a single task, ready to be picked up by the agents (no limit on the amount of agents)
    $response = $this->createTask([
      "name" => "task-1",
      "hashlistId" => $hashlistId,
      "attackCmd" => "#HL# -a 0 -r best64.rule example.dict",
      "priority" => 100,
      "color" => "FFFFFF",
      "crackerVersionId" => 1,
      "files" => [1]]);

    $task1Id = $response["taskId"];

    // verify agent 1 is assigned to task 1
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent1["token"]]);
    if ($response["taskId"] != $task1Id) {
      $this->testFailed("MaxAgentsTest:testMaxAgents()", sprintf("Expected task with id '%d' for agent 1, instead got: %s", $task1Id, implode(", ", $response)));
      return;
    }

    // verify agent 2 is assigned to task 1
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent2["token"]]);
    if ($response["taskId"] != $task1Id) {
      $this->testFailed("MaxAgentsTest:testMaxAgents()", sprintf("Expected task with id '%d' for agent 2, instead got: %s", $task1Id, implode(", ", $response)));
      return;
    }

    // after the task is assigned, make sure to set the keyspace and benchmark parameters
    $response = HashtopolisTestFramework::doRequest([
      "action" => "sendKeyspace",
      "taskId" => $task1Id,
      "token" => $agent2["token"],
      "keyspace" => 100]);

    $response = HashtopolisTestFramework::doRequest([
      "action" => "sendBenchmark",
      "taskId" => $task1Id,
      "token" => $agent2["token"],
      "type" => "run",
      "result" => 2000000000]);

    // now set the task to only allow 1 agent to work on it
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "setTaskMaxAgents",
      "accessKey" => "mykey",
      "taskId" => $task1Id,
      "maxAgents" => 1
    ], HashtopolisTestFramework::REQUEST_UAPI);

    // verify agent 2 is NOT assigned to task 1
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent2["token"]]);
    if ($response["taskId"] == $task1Id) {
      $this->testFailed("MaxAgentsTest:testMaxAgents()", sprintf("Expected no task for agent 2, instead got: %s", implode(", ", $response)));
      return;
    }
    // verify getting chunk by agent 2 for task 1 now fails, because the task is already saturated
    $response = HashtopolisTestFramework::doRequest([
      "action" => "getChunk",
      "taskId" => $task1Id,
      "token" => $agent2["token"]]);
    if ($response["response"] !== "ERROR" || $response["message"] != "Task already saturated by other agents, no other task available!") {
      $this->testFailed("MaxAgentsTest:testMaxAgents()", sprintf("Expected getChunk to fail, instead got: %s", implode(", ", $response)));
      return;
    }

    // actually unassign agent 2
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "taskUnassignAgent",
      "accessKey" => "mykey",
      "agentId" => $agent2["agentId"]
    ], HashtopolisTestFramework::REQUEST_UAPI);

    // verify agent 1 is assigned to task 1
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent1["token"]]);
    if ($response["taskId"] != $task1Id) {
      $this->testFailed("MaxAgentsTest:testMaxAgents()", sprintf("Expected task with id '%d' for agent 1, instead got: %s", $task1Id, implode(", ", $response)));
      return;
    }

    // now create a second task
    $response = $this->createTask([
      "name" => "task-2",
      "hashlistId" => $hashlistId,
      "attackCmd" => "#HL# -a 0 -r best64.rule example.dict",
      "priority" => 10,
      "color" => "FFFFFF",
      "crackerVersionId" => 1,
      "files" => [2]
    ]);
    $task2Id = $response["taskId"];

    // verify agent 1 is assigned to task 1
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent1["token"]]);
    if ($response["taskId"] != $task1Id) {
      $this->testFailed("MaxAgentsTest:testMaxAgents()", sprintf("Expected task with id '%d' for agent 1, instead got: %s", $task1Id, implode(", ", $response)));
      return;
    }

    // verify agent 2 is assigned to task 2
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent2["token"]]);
    if ($response["taskId"] != $task2Id) {
      $this->testFailed("MaxAgentsTest:testMaxAgents()", sprintf("Expected task with id '%d' for agent 1, instead got: %s", $task2Id, implode(", ", $response)));
      return;
    }

    // verify getting chunk by agent 2 for task 2 succeeds
    $response = HashtopolisTestFramework::doRequest([
      "action" => "getChunk",
      "taskId" => $task2Id,
      "token" => $agent2["token"]]);
    if ($response["response"] !== "SUCCESS") {
      $this->testFailed("MaxAgentsTest:testMaxAgents()", sprintf("Expected getChunk to succeed, instead got: %s", implode(", ", $response)));
      return;
    }

    // now set the task to allow any amount of agents to work on it
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "setTaskMaxAgents",
      "accessKey" => "mykey",
      "taskId" => $task1Id,
      "maxAgents" => 0
    ], HashtopolisTestFramework::REQUEST_UAPI);

    // verify agent 2 is assigned to task 1 (since it has higher priority than task 2)
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent2["token"]]);
    if ($response["taskId"] != $task1Id) {
      $this->testFailed("MaxAgentsTest:testMaxAgents()", sprintf("Expected task with id '%d' for agent 1, instead got: %s", $task1Id, implode(", ", $response)));
      return;
    }

    // verify getting chunk by agent 2 for task 1 succeeds
    $response = HashtopolisTestFramework::doRequest([
      "action" => "getChunk",
      "taskId" => $task1Id,
      "token" => $agent2["token"]]);
    if ($response["response"] !== "SUCCESS") {
      $this->testFailed("MaxAgentsTest:testMaxAgents()", sprintf("Expected getChunk to succeed, instead got: %s", implode(", ", $response)));
      return;
    }
    $this->testSuccess("MaxAgentsTest:testMaxAgents()");
  }

  private function addFile($name, $type) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "addFile",
      "filename" => $name,
      "fileType" => $type,
      "source" => "inline",
      "data" => base64_encode(file_get_contents(dirname(__FILE__) . "/../../files/$name")),
      "accessGroupId" => 1,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      return false;
    }
    else if ($response['response'] != 'OK') {
      return false;
    }
    return true;
  }

  private function addHashlist($values = []) {
    $data = base64_encode(file_get_contents(dirname(__FILE__) . "/../../files/example0.hash"));
    $query = [
      "section" => "hashlist",
      "request" => "createHashlist",
      "name" => "Test Hashlist",
      "isSalted" => false,
      "isSecret" => true,
      "isHexSalt" => false,
      "separator" => ":",
      "format" => 0,
      "hashtypeId" => 0,
      "accessGroupId" => 1,
      "data" => $data,
      "useBrain" => false,
      "brainFeatures" => 0,
      "accessKey" => "mykey"
    ];
    foreach ($values as $key => $value) {
      $query[$key] = $value;
    };
    return HashtopolisTestFramework::doRequest($query, HashtopolisTestFramework::REQUEST_UAPI);
  }

  private function createAgent($name) {
    $voucher = "voucher-" . $name;
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "createVoucher",
      "voucher" => $voucher,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI);
    $response = HashtopolisTestFramework::doRequest([
      "action" => "register",
      "voucher" => $voucher,
      "name" => $name]);
    $token = $response['token'];
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "listAgents",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI);
    $agent = current(array_filter($response["agents"], function($a) use ($name) {
      return $a["name"] == $name;
    }));
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "setTrusted",
      "accessKey" => "mykey",
      "agentId" => $agent["agentId"],
      "trusted" => true
    ], HashtopolisTestFramework::REQUEST_UAPI);
    return array("agentId" => $agent["agentId"], "token" => $token);
  }

  private function createTask($values = []) {
    $query = [
      "section" => "task",
      "request" => "createTask",
      "name" => "",
      "hashlistId" => 0,
      "attackCmd" => "",
      "chunksize" => 600,
      "statusTimer" => 5,
      "benchmarkType" => "speed",
      "color" => "",
      "isCpuOnly" => false,
      "isSmall" => false,
      "skip" => 0,
      "crackerVersionId" => 0,
      "files" => [],
      "priority" => 0,
      "maxAgents" => 0,
      "preprocessorId" => 0,
      "preprocessorCommand" => "",
      "accessKey" => "mykey"
    ];
    foreach ($values as $key => $value) {
      $query[$key] = $value;
    }
    return HashtopolisTestFramework::doRequest($query, HashtopolisTestFramework::REQUEST_UAPI);
  }

  public function getTestName() {
    return "Max Agents Test";
  }
}

HashtopolisTestFramework::register(new  MaxAgentsTest());