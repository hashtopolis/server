<?php

class TaskTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
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
    
    // add a hashlist
    $status &= $this->addHashlist();
    
    if (!$status) {
      HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_ERROR, "Some initialization failed, most likely tests will fail!");
    }
  }
  
  private function cleanup() {
    $status = true;
    // delete the created task
    $status &= $this->deleteTaskIfExists("Test Task");

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
      $this->testListTasks();
      $this->testCreateTask(["name" => "Test Task", "hashlistId" => 1, "attackCmd" => "#HL# -a 0 -r best64.rule example.dict", "priority" => 1, "color" => "5D5D5D", "crackerVersionId" => 1, "files" => [1, 2]]);
      $this->testListTasks(['Test Task']);
      $this->testGetTask(1, [
        "taskId" => 1,
        "name" => "Test Task",
        "hashlistId" => 1,
        "attack" => "#HL# -a 0 -r best64.rule example.dict",
        "chunksize" => 600,
        "priority" => 1,
        "color" => "5D5D5D",
        "benchmarkType" => "speed",
        "statusTimer" => 5,
        "isCpuOnly" => false,
        "isSmall" => false,
        "skipKeyspace" => 0,
        "keyspace" => 0,
        "dispatched" => 0,
        "imageUrl" => "http://localhost/api/taskimg.php?task=1",
        "speed" => 0,
        "searched" => 0,
        "chunkIds" => [],
        "agents" => [],
        "isComplete" => false,
        "usePreprocessor" => false,
        "preprocessorId" => 0,
        "preprocessorCommand" => "",
        "files" => [
          [
            "fileId" => 1,
            "filename" => "example.dict",
            "size" => 1080240
          ],
          [
            "fileId" => 2,
            "filename" => "best64.rule",
            "size" => 1035
          ]
        ]
      ]);
    }
    finally {
      $this->cleanup();
    }

    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }
  
  private function testGetTask($taskId, $assert = []) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "getTask",
      "taskId" => $taskId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("TaskTest:testGetTask($taskId," . implode(",", $assert) . ")", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("TaskTest:testGetTask($taskId," . implode(",", $assert) . ")", "Response not OK");
    }
    else {
      foreach ($assert as $key => $value) {
        if (!isset($response[$key])) {
          $this->testFailed("TaskTest:testGetTask($taskId," . implode(",", $assert) . ")", "$key not in response but in assert");
          return;
        }
        else if ($response[$key] != $value) {
          $this->testFailed("TaskTest:testGetTask($taskId," . implode(",", $assert) . ")", "Value from key ($key) not in response but in assert");
          return;
        }
      }
      $this->testSuccess("TaskTest:testGetTask($taskId," . implode(",", $assert) . ")");
    }
  }
  
  public function testCreateTask($values = [], $assert = true) {
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
    
    $response = HashtopolisTestFramework::doRequest($query, HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("TaskTest:testCreateTask", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("TaskTest:testCreateTask(" . HashtopolisTest::multiImplode(",", $values) . ",$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("TaskTest:testListTasks");
    }
  }
  
  private function testListTasks($assert = []) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "task",
      "request" => "listTasks",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("TaskTest:testListTasks(" . implode(", ", $assert) . ")", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("TaskTest:testListTasks(" . implode(", ", $assert) . ")", "Response not OK");
    }
    else if (sizeof($assert) != sizeof($response['tasks'])) {
      $this->testFailed("TaskTest:testListTasks(" . implode(", ", $assert) . ")", "Not matching number of tasks");
    }
    else {
      foreach ($response['tasks'] as $task) {
        if (!in_array($task['name'], $assert)) {
          $this->testFailed("TaskTest:testListTasks(" . implode(", ", $assert) . ")", "Not matching task name");
          return;
        }
      }
      $this->testSuccess("TaskTest:testListTasks(" . implode(", ", $assert) . ")");
    }
  }
  
  private function addHashlist() {
    $data = base64_encode(file_get_contents(dirname(__FILE__) . "/../files/example0.hash"));
    $hashtype = 0;
    $response = HashtopolisTestFramework::doRequest([
      "section" => "hashlist",
      "request" => "createHashlist",
      "name" => "Test Hashlist",
      "isSalted" => false,
      "isSecret" => true,
      "isHexSalt" => false,
      "separator" => ":",
      "format" => 0,
      "hashtypeId" => $hashtype,
      "accessGroupId" => 1,
      "data" => $data,
      "useBrain" => false,
      "brainFeatures" => 0,
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
  
  private function addFile($name, $type) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "addFile",
      "filename" => $name,
      "fileType" => $type,
      "source" => "inline",
      "data" => base64_encode(file_get_contents(dirname(__FILE__) . "/../files/$name")),
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

  public function getTestName() {
    return "Task Test";
  }
}
HashtopolisTestFramework::register(new TaskTest());
