<?php


class RuleSplitTest extends HashtopolisTest {
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
    $status &= $this->deleteTaskIfExists("task-1 (From Rule Split)");
    $status &= $this->deleteHashlistIfExists("Test Rule Split");

    // remove the added files
    $status &= $this->deleteFileIfExists("example.dict");
    $status &= $this->deleteFileIfExists("best64.rule");

    $status &= $this->setConfig('ruleSplitDisable', true);
    $status &= $this->setConfig('ruleSplitAlways', false);

    if (!$status) {
      HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_ERROR, "Some cleanup failed, deleting task or deleting files not succesful!");
    }
  }

  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    $this->prepare();
    try {
      $this->testRuleSplit();
    }
    finally {
      $this->cleanup();
    }
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }

  private function testRuleSplit() {
    # example.dict / best64.rule
    $file_id1 = $this->getFile('example.dict');
    $file_id2 = $this->getFile('best64.rule');

    # Create hashlist
    $response = $this->addHashlist(["isSecure" => false]);
    $hashlistId = $response["hashlistId"];
  
    # Create task with rule/wordlist
    $response = $this->createTask([
      "name" => "task-1",
      "hashlistId" => $hashlistId,
      "attackCmd" => "#HL# -a 0 -r best64.rule example.dict",
      "priority" => 100,
      "color" => "FFFFFF",
      "crackerVersionId" => 1,
      "files" => [$file_id1, $file_id2],
      "chunksize" => 1],
    );

    $task1Id = $response["taskId"];

    if ($task1Id === null) {
      $this->testFailed("RuleSplitTest:testRuleSplit()", sprintf("Failed to create task."));
      return;
    }

    # Enable rulesplit
    $this->setConfig('ruleSplitDisable', false);
    $this->setConfig('ruleSplitAlways', true);

    # Create agent
    $agent = $this->createAgent("agent-1");
    HashtopolisTestFramework::doRequest(["action" => "getTask", "token" => $agent["token"]]);

    # keyspace
    $response = HashtopolisTestFramework::doRequest([
      "action" => "sendKeyspace",
      "taskId" => $task1Id,
      "token" => $agent["token"],
      "keyspace" => 10000000]
    );
    # benchmark
    $response = HashtopolisTestFramework::doRequest([
      "action" => "sendBenchmark",
      "taskId" => $task1Id,
      "token" => $agent["token"],
      "type" => "speed",
      "result" => '2000:2200000']
    );
    if (!is_array($response)) {
      $this->testFailed("RuleSplitTest:testRuleSplit()", sprintf("Expected benchmark to return OK."));
    } else {
      if ($this->getTask('task-1 (From Rule Split)')) {
        $this->testSuccess("RuleSplitTest:testRuleSplit()");
      } else {
        $this->testFailed("RuleSplitTest:testRuleSplit()", sprintf("Couldn't find the created supertask"));
      }
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

  private function addHashlist($values = []) {
    $data = base64_encode(file_get_contents(dirname(__FILE__) . "/../../files/example0.hash"));
    $query = [
      "section" => "hashlist",
      "request" => "createHashlist",
      "name" => "Test Rule Split",
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

  private function getTask($name) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "listTasks",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response['response'] == 'OK') {
      foreach ($response['tasks'] as $task) {
        if ($task['name'] == $name) {
          return $task;
        }
      }
    }
    return false;
  }

  private function getFile($name) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "listFiles",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response['response'] == 'OK') {
      foreach ($response['files'] as $file) {
        if ($file['filename'] == $name) {
          return $file['fileId'];
        }
      }
    }
    return false;
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

  private function setConfig($item, $value, $force = false) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "config",
      "request" => "setConfig",
      "configItem" => $item,
      "value" => $value,
      "force" => $force,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      return false;
    } else {
      return true;
    }
  }

  
  public function getTestName() {
    return "Rule Split Test";
  }
}

HashtopolisTestFramework::register(new  RuleSplitTest());