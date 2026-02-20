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

    // delete the added agents
    $status &= $this->deleteAgent("agent-1");
    $status &= $this->deleteAgent("agent-2");
    $status &= $this->deleteAgent("agent-1013-1");
    $status &= $this->deleteAgent("agent-1013-2");
    $status &= $this->deleteAgent("agent-pt-1");
    $status &= $this->deleteAgent("agent-pt-2");
    $status &= $this->deleteAgent("agent-pt-3");

    if (!$status) {
      HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_ERROR, "Some cleanup failed, deleting task or deleting files not succesful!");
    }
  }

  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    $this->prepare();
    try {
      $response = $this->addHashlist(["name" => "NotSecureList", "isSecure" => false])["hashlist"];
      $hashlistId = $response["hashlistId"];

      $this->testTaskMaxAgents($hashlistId);
      $this->testTaskMaxAgents_bug_1013($hashlistId);
      $this->testSuperTaskMaxAgents($hashlistId);
    }
    finally {
      $this->cleanup();
    }
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }

  private function testTaskMaxAgents($hashlistId) {
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
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents()", sprintf("Expected task with id '%d' for agent 1, instead got: %s", $task1Id, implode(", ", $response)));
      return;
    }

    // verify agent 2 is assigned to task 1
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent2["token"]]);
    if ($response["taskId"] != $task1Id) {
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents()", sprintf("Expected task with id '%d' for agent 2, instead got: %s", $task1Id, implode(", ", $response)));
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
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents()", sprintf("Expected no task for agent 2, instead got: %s", implode(", ", $response)));
      return;
    }
    // verify getting chunk by agent 2 for task 1 now fails, because the task is already saturated
    $response = HashtopolisTestFramework::doRequest([
      "action" => "getChunk",
      "taskId" => $task1Id,
      "token" => $agent2["token"]]);
    if ($response["response"] !== "ERROR" || $response["message"] != "You are not assigned to this task!") {
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents()", sprintf("Expected getChunk to fail, instead got: %s", implode(", ", $response)));
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
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents()", sprintf("Expected task with id '%d' for agent 1, instead got: %s", $task1Id, implode(", ", $response)));
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
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents()", sprintf("Expected task with id '%d' for agent 1, instead got: %s", $task1Id, implode(", ", $response)));
      return;
    }

    // verify agent 2 is assigned to task 2
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent2["token"]]);
    if ($response["taskId"] != $task2Id) {
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents()", sprintf("Expected task with id '%d' for agent 2, instead got: %s", $task2Id, implode(", ", $response)));
      return;
    }

    // verify getting chunk by agent 2 for task 2 succeeds
    $response = HashtopolisTestFramework::doRequest([
      "action" => "getChunk",
      "taskId" => $task2Id,
      "token" => $agent2["token"]]);
    if ($response["response"] !== "SUCCESS") {
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents()", sprintf("Expected getChunk to succeed, instead got: %s", implode(", ", $response)));
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
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents()", sprintf("Expected task with id '%d' for agent 1, instead got: %s", $task1Id, implode(", ", $response)));
      return;
    }

    // verify getting chunk by agent 2 for task 1 succeeds
    $response = HashtopolisTestFramework::doRequest([
      "action" => "getChunk",
      "taskId" => $task1Id,
      "token" => $agent2["token"]]);
    if ($response["response"] !== "SUCCESS") {
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents()", sprintf("Expected getChunk to succeed, instead got: %s", implode(", ", $response)));
      return;
    }
    $this->testSuccess("MaxAgentsTest:testTaskMaxAgents()");
  }

  private function testTaskMaxAgents_bug_1013($hashlistId) {
    $agent1 = $this->createAgent("agent-1013-1");
    $agent2 = $this->createAgent("agent-1013-2");

    // disable existing tasks
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "listTasks",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI);
    foreach ($response["tasks"] as $task) {
      $this->setTaskPriority($task["taskId"], 0);
    }

    // Register a single task, max agents set to 1
    // Agent 1 should get the task, calculate keyspace and benchmark
    // Set max agents to 2, agent 2 should get the task

    $response = $this->createTask([
      "name" => "task-1",
      "hashlistId" => $hashlistId,
      "attackCmd" => "#HL# -a 0 -r best64.rule example.dict",
      "priority" => 100,
      "color" => "FFFFFF",
      "crackerVersionId" => 1,
      "files" => [],
      "maxAgents" => 1]);

    $task1Id = $response["taskId"];

    // verify agent 1 is assigned to task 1
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent1["token"]]);
    if ($response["taskId"] != $task1Id) {
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents_bug_1013()", sprintf("Expected task with id '%d' for agent 1, instead got: %s", $task1Id, implode(", ", $response)));
      return;
    }

    // now set the task to only allow 2 agent to work on it
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "setTaskMaxAgents",
      "accessKey" => "mykey",
      "taskId" => $task1Id,
      "maxAgents" => 2
    ], HashtopolisTestFramework::REQUEST_UAPI);

    // verify agent 2 is NOT assigned to task 1
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent2["token"]]);
    if ($response["taskId"] != $task1Id) {
      $this->testFailed("MaxAgentsTest:testTaskMaxAgents_bug_1013()", sprintf("Expected task with id '%d' for agent 2", implode(", ", $response)));
      return;
    }

    $this->testSuccess("MaxAgentsTest:testTaskMaxAgents_bug_1013()");
  }

  private function testSuperTaskMaxAgents($hashlistId) {
    // disable existing tasks
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "listTasks",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI);
    foreach ($response["tasks"] as $task) {
      $this->setTaskPriority($task["taskId"], 0);
    }

    // register 3 agents
    $agent1 = $this->createAgent("agent-pt-1");
    $agent2 = $this->createAgent("agent-pt-2");
    $agent3 = $this->createAgent("agent-pt-3");

    // create 3 pretasks
    $response = $this->createPretask([
      "name" => "pretask-1",
      "attackCmd" => "#HL# -a 3 ?l?l?l?l?l?l",
      "priority" => 1,
      "maxAgents" => 0
    ]);
    $response = $this->createPretask([
      "name" => "pretask-2",
      "attackCmd" => "#HL# -a 3 ?l?l?l?l?l?l",
      "priority" => 0,
      "maxAgents" => 1
    ]);
    $response = $this->createPretask([
      "name" => "pretask-3",
      "attackCmd" => "#HL# -a 3 ?l?l?l?l?l?l",
      "priority" => 100,
      "maxAgents" => 2
    ]);

    // collect pretask information
    $response = HashtopolisTestFramework::doRequest([
      "section" => "pretask",
      "request" => "listPretasks",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI);
    $pretasks = array_map(fn($pretask) => $pretask["pretaskId"], $response["pretasks"]);

    // create a supertask for all pretasks above, and get the supertask id
    $response = HashtopolisTestFramework::doRequest([
      "section" => "supertask",
      "request" => "createSupertask",
      "accessKey" => "mykey",
      "name" => "supertask-1",
      "pretasks" => $pretasks
    ], HashtopolisTestFramework::REQUEST_UAPI);
    $response = HashtopolisTestFramework::doRequest([
      "section" => "supertask",
      "request" => "listSupertasks",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI);
    $supertaskId = $response["supertasks"][0]["supertaskId"];

    // execute the supertask
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "runSupertask",
      "accessKey" => "mykey",
      "supertaskId" => $supertaskId,
      "hashlistId" => $hashlistId,
      "crackerVersionId" => 1
    ], HashtopolisTestFramework::REQUEST_UAPI);

    // get the taskwrapper created from the supertask
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "listTasks",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI);
    $taskWrapper = current(array_filter($response["tasks"], fn($task) => $task["name"] == "supertask-1"));

    // Check if the max agents of the wrapper is 0, since it should not take over the maxAgents value of the pretasks
    if ($taskWrapper["maxAgents"] != 0) {
      $this->testFailed("MaxAgentsTest:testSupertaskMaxAgents()", sprintf("Expected taskwrapper to have maxAgents value of 0, instead got: %s", $taskWrapper["maxAgents"]));
      return;
    }

    // request a task for agent 1
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent1["token"]]);
    $task = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "getTask",
      "accessKey" => "mykey",
      "taskId" => $response["taskId"]
    ], HashtopolisTestFramework::REQUEST_UAPI);
    // check that agent 1 is assigned to pretask-3, as it has the highest priority
    if ($task["name"] != "pretask-3") {
      $this->testFailed("MaxAgentsTest:testSupertaskMaxAgents()", sprintf("Expected agent 1 to be assigned to pretask-3, instead assigned to to: %s", $task["name"]));
      return;
    }

    // now update the max allowed agents of the supertask to 1
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "setSupertaskMaxAgents",
      "accessKey" => "mykey",
      "supertaskId" => $taskWrapper["supertaskId"],
      "supertaskMaxAgents" => 1
    ], HashtopolisTestFramework::REQUEST_UAPI);

    // request a task for agent 2
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent2["token"]]);
    // check that no task is given, since the limit of 1 max agent on the supertask has been reached
    if ($response["taskId"] != null) {
      $this->testFailed("MaxAgentsTest:testSupertaskMaxAgents()", sprintf("Expected no task for agent 2, instead got task with id: %s", $response["taskId"]));
      return;
    }

    // now update the max allowed agents of the supertask to 2
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "setSupertaskMaxAgents",
      "accessKey" => "mykey",
      "supertaskId" => $taskWrapper["supertaskId"],
      "supertaskMaxAgents" => 2
    ], HashtopolisTestFramework::REQUEST_UAPI);

    // request a task for agent 2
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent2["token"]]);
    $task = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "getTask",
      "accessKey" => "mykey",
      "taskId" => $response["taskId"]
    ], HashtopolisTestFramework::REQUEST_UAPI);
    // check that agent 2 is now assigned to pretask-3, as it has the highest priority and has maxAgents value of 2
    if ($task["name"] != "pretask-3") {
      $this->testFailed("MaxAgentsTest:testSupertaskMaxAgents()", sprintf("Expected agent 2 to be assigned to pretask-3, instead assigned to to: %s", $task["name"]));
      return;
    }

    // request a task for agent 3
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent3["token"]]);
    // check that no task is given, since the limit of 2 max agents on the supertask has been reached
    if ($response["taskId"] != null) {
      $this->testFailed("MaxAgentsTest:testSupertaskMaxAgents()", sprintf("Expected no task for agent 3, instead got task with id: %s", $response["taskId"]));
      return;
    }

    // now update the max allowed agents of the supertask to unlimited
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "setSupertaskMaxAgents",
      "accessKey" => "mykey",
      "supertaskId" => $taskWrapper["supertaskId"],
      "supertaskMaxAgents" => 0
    ], HashtopolisTestFramework::REQUEST_UAPI);

    // request a task for agent 3
    $response = HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent3["token"]]);
    $task = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "getTask",
      "accessKey" => "mykey",
      "taskId" => $response["taskId"]
    ], HashtopolisTestFramework::REQUEST_UAPI);
    // check that agent 3 is now assigned to pretask-1, as pretask-3 is saturated and pretask-2 has 0 priority
    if ($task["name"] != "pretask-1") {
      $this->testFailed("MaxAgentsTest:testSupertaskMaxAgents()", sprintf("Expected agent 3 to be assigned to pretask-1, instead assigned to to: %s", $task["name"]));
      return;
    }

    $this->testSuccess("MaxAgentsTest:testSuperTaskMaxAgents()");
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

  private function deleteAgent($name) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "listAgents",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI);
    $agent = current(array_filter($response["agents"], function($a) use ($name) {
      return $a["name"] == $name;
    }));
    // if agent doesn't exists return true
    if ($agent == null) {
      return true;
    }
    $response = HashtopolisTestFramework::doRequest([
      "section" => "agent",
      "request" => "deleteAgent",
      "accessKey" => "mykey",
      "agentId" => $agent["agentId"]
    ], HashtopolisTestFramework::REQUEST_UAPI);
    // if response is success return true
    if ($response["response"] == "OK") {
      return true;
    } else {
      return false;
    }
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

  private function createPretask($values = []) {
    $query = [
      "section" => "pretask",
      "request" => "createPretask",
      "name" => "",
      "attackCmd" => "",
      "chunksize" => 600,
      "statusTimer" => 5,
      "benchmarkType" => "speed",
      "color" => "",
      "isCpuOnly" => false,
      "isSmall" => false,
      "crackerTypeId" => 1,
      "files" => [],
      "priority" => 0,
      "maxAgents" => 16,
      "accessKey" => "mykey"
    ];
    foreach ($values as $key => $value) {
      $query[$key] = $value;
    }
    return HashtopolisTestFramework::doRequest($query, HashtopolisTestFramework::REQUEST_UAPI);
  }

  private function setTaskPriority($taskId, $priority) {
    return HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "setTaskPriority",
      "accessKey" => "mykey",
      "taskId" => $taskId,
      "priority" => $priority
    ], HashtopolisTestFramework::REQUEST_UAPI);
  }

  public function getTestName() {
    return "Max Agents Test";
  }
}

HashtopolisTestFramework::register(new  MaxAgentsTest());
