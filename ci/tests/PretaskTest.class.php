<?php

/**
 * @deprecated
 */
class PretaskTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType    = HashtopolisTest::RUN_FAST;
  
  public function init($version) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing " . $this->getTestName() . "...");
    parent::init($version);
  }
  
  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    $this->testListPretasks([]);
    $this->testGetPretask(1, [], [], false);
    $this->testCreatePretask("Pretask #1", "#HL# -a 3 ?l?l?l?l?l?l");
    $this->testGetPretask(1, ['pretaskId' => 1, 'name' => "Pretask #1", 'attackCmd' => "#HL# -a 3 ?l?l?l?l?l?l", 'priority' => 0]);
    $this->testListPretasks([1 => ["name" => "Pretask #1", "priority" => 0]]);
    $this->testCreatePretask("Pretask fail", "-a 3 ?l?l?l?l?l?l", false); // hashlist alias is not in attack command
    $this->testCreatePretask("", "#HL# -a 3 ?l?l?l?l?l?l", false); // empty task name
    $this->testCreatePretask("Pretask #2", "#HL# -a 3 ?l?l?l?l?l?l", true, 0); // chunk size 0 (will go to default)
    $this->testCreatePretask("Pretask #3", "#HL# -a 3 ?l?l?l?l?l?l", true, -600); // chunk size negative (will go to default)
    $this->testGetPretask(2, ['pretaskId' => 2, 'name' => "Pretask #2", 'attackCmd' => "#HL# -a 3 ?l?l?l?l?l?l", 'priority' => 0, 'chunksize' => 600]);
    $this->testGetPretask(3, ['pretaskId' => 3, 'name' => "Pretask #3", 'attackCmd' => "#HL# -a 3 ?l?l?l?l?l?l", 'priority' => 0, 'chunksize' => 600]);
    $this->testCreatePretask("Pretask fail", "#HL# -a 3 ?l?l?l?l?l?l", false, 600, 5, 'speed', 2); // invalid cracker type
    $this->testSetPretaskPriority(5, 5, false); // invalid pretask
    $this->testSetPretaskPriority(1, "bla", false); // invalid priority
    $this->testSetPretaskPriority(1, 5);
    $this->testGetPretask(1, ['pretaskId' => 1, 'name' => "Pretask #1", 'attackCmd' => "#HL# -a 3 ?l?l?l?l?l?l", 'priority' => 5]);
    $this->testListPretasks([1 => ['name' => "Pretask #1", 'priority' => 5], 2 => ['name' => "Pretask #2", 'priority' => 0], 3 => ['name' => "Pretask #3", 'priority' => 0]]);
    $this->testSetPretaskName(1, "", false); // empty name
    $this->testSetPretaskName(10, "Name", false); // invalid id
    $this->testSetPretaskName(1, "Pretask Name");
    $this->testGetPretask(1, ['pretaskId' => 1, 'name' => "Pretask Name", 'attackCmd' => "#HL# -a 3 ?l?l?l?l?l?l", 'priority' => 5]);
    $this->testSetPretaskColor(1, "hello", false); // not valid html color
    $this->testSetPretaskColor(10, "ff00ff", false); // invalid id
    $this->testSetPretaskColor(1, "ff00ff");
    $this->testGetPretask(1, ['pretaskId' => 1, 'name' => "Pretask Name", 'attackCmd' => "#HL# -a 3 ?l?l?l?l?l?l", 'priority' => 5, 'color' => 'ff00ff']);
    $this->testSetPretaskColor(1, "");
    $this->testGetPretask(1, ['pretaskId' => 1, 'name' => "Pretask Name", 'attackCmd' => "#HL# -a 3 ?l?l?l?l?l?l", 'priority' => 5, 'color' => null]);
    $this->testSetPretaskChunksize(1, -5, false); // invalid chunk size
    $this->testSetPretaskChunksize(1, 0, false); // invalid chunk size
    $this->testSetPretaskChunksize(10, 100, false); // invalid id
    $this->testSetPretaskChunksize(1, 6000);
    $this->testGetPretask(1, ['pretaskId' => 1, 'name' => "Pretask Name", 'attackCmd' => "#HL# -a 3 ?l?l?l?l?l?l", 'priority' => 5, 'chunksize' => 6000]);
    $this->testSetPretaskCpuOnly(10, true, false); // invalid id
    $this->testSetPretaskCpuOnly(1, "hello", false); // invalid setting
    $this->testSetPretaskCpuOnly(1, true);
    $this->testGetPretask(1, ['pretaskId' => 1, 'name' => "Pretask Name", 'isCpuOnly' => true]);
    $this->testSetPretaskCpuOnly(1, false);
    $this->testSetPretaskSmall(10, true, false); // invalid id
    $this->testSetPretaskSmall(1, "hello", false); // invalid setting
    $this->testSetPretaskSmall(1, true);
    $this->testGetPretask(1, ['pretaskId' => 1, 'name' => "Pretask Name", 'isSmall' => true, 'isCpuOnly' => false]);
    $this->testSetPretaskSmall(1, false);
    $this->testDeletePretask(10, false); // invalid id
    $this->testDeletePretask(1);
    $this->testGetPretask(1, [], [], false);
    $this->testListPretasks([2 => ['name' => "Pretask #2", 'priority' => 0], 3 => ['name' => "Pretask #3", 'priority' => 0]]);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }
  
  private function testDeletePretask($pretaskId, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "pretask",
      "request" => "deletePretask",
      "pretaskId" => $pretaskId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("PretaskTest:testDeletePretask($pretaskId,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("PretaskTest:testDeletePretask($pretaskId,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("PretaskTest:testDeletePretask($pretaskId,$assert)");
    }
  }
  
  private function testSetPretaskSmall($pretaskId, $isSmall, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "pretask",
      "request" => "setPretaskSmall",
      "pretaskId" => $pretaskId,
      "isSmall" => $isSmall,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("PretaskTest:testSetPretaskSmall($pretaskId,$isSmall,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("PretaskTest:testSetPretaskSmall($pretaskId,$isSmall,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("PretaskTest:testSetPretaskSmall($pretaskId,$isSmall,$assert)");
    }
  }
  
  private function testSetPretaskCpuOnly($pretaskId, $isCpuOnly, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "pretask",
      "request" => "setPretaskCpuOnly",
      "pretaskId" => $pretaskId,
      "isCpuOnly" => $isCpuOnly,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("PretaskTest:testSetPretaskCpuOnly($pretaskId,$isCpuOnly,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("PretaskTest:testSetPretaskCpuOnly($pretaskId,$isCpuOnly,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("PretaskTest:testSetPretaskCpuOnly($pretaskId,$isCpuOnly,$assert)");
    }
  }
  
  private function testSetPretaskChunksize($pretaskId, $chunksize, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "pretask",
      "request" => "setPretaskChunksize",
      "pretaskId" => $pretaskId,
      "chunksize" => $chunksize,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("PretaskTest:testSetPretaskChunksize($pretaskId,$chunksize,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("PretaskTest:testSetPretaskChunksize($pretaskId,$chunksize,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("PretaskTest:testSetPretaskChunksize($pretaskId,$chunksize,$assert)");
    }
  }
  
  private function testSetPretaskColor($pretaskId, $color, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "pretask",
      "request" => "setPretaskColor",
      "pretaskId" => $pretaskId,
      "color" => $color,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("PretaskTest:testSetPretaskName($pretaskId,$color,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("PretaskTest:testSetPretaskColor($pretaskId,$color,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("PretaskTest:testSetPretaskColor($pretaskId,$color,$assert)");
    }
  }
  
  private function testSetPretaskName($pretaskId, $name, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "pretask",
      "request" => "setPretaskName",
      "pretaskId" => $pretaskId,
      "name" => $name,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("PretaskTest:testSetPretaskName($pretaskId,$name,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("PretaskTest:testSetPretaskName($pretaskId,$name,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("PretaskTest:testSetPretaskName($pretaskId,$name,$assert)");
    }
  }
  
  private function testSetPretaskPriority($pretaskId, $priority, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "pretask",
      "request" => "setPretaskPriority",
      "pretaskId" => $pretaskId,
      "priority" => $priority,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("PretaskTest:testChangePassword($pretaskId,$priority,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("PretaskTest:testChangePassword($pretaskId,$priority,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("PretaskTest:testChangePassword($pretaskId,$priority,$assert)");
    }
  }
  
  private function testListPretasks($data, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "pretask",
      "request" => "listPretasks",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("PretaskTest:testListPretasks([" . HashtopolisTest::multiImplode(", ", $data) . "],$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("PretaskTest:testListPretasks([" . HashtopolisTest::multiImplode(", ", $data) . "],$assert)", "Response does not match assert");
    }
    else {
      if (!$assert) {
        $this->testSuccess("PretaskTest:testListPretasks([" . HashtopolisTest::multiImplode(", ", $data) . "],$assert)");
        return;
      }
      else if (sizeof($response['pretasks']) != sizeof($data)) {
        $this->testFailed("PretaskTest:testListPretasks([" . HashtopolisTest::multiImplode(", ", $data) . "],$assert)", "Response OK, but number of entries not matching");
        return;
      }
      foreach ($response['pretasks'] as $pretask) {
        foreach ($data[$pretask['pretaskId']] as $key => $val) {
          if (!array_key_exists($key, $pretask) || $val != $pretask[$key]) {
            $this->testFailed("PretaskTest:testListPretasks([" . HashtopolisTest::multiImplode(", ", $data) . "],$assert)", "Response OK, but wrong content");
            return;
          }
        }
      }
      $this->testSuccess("PretaskTest:testListPretasks([" . HashtopolisTest::multiImplode(", ", $data) . "],$assert)");
    }
  }
  
  private function testCreatePretask($name, $cmd, $assert = true, $chunksize = 600, $statusTimer = 5, $benchmarkType = "speed", $crackerTypeId = 1, $files = [], $priority = 0, $color = "", $isCpuOnly = false, $isSmall = false) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "pretask",
      "request" => "createPretask",
      "name" => $name,
      "attackCmd" => $cmd,
      "chunksize" => $chunksize,
      "statusTimer" => $statusTimer,
      "benchmarkType" => $benchmarkType,
      "color" => $color,
      "isCpuOnly" => $isCpuOnly,
      "isSmall" => $isSmall,
      "crackerTypeId" => $crackerTypeId,
      "files" => $files,
      "priority" => $priority,
      "maxAgents" => 16,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("PretaskTest:testCreatePretask($name,$cmd,$chunksize,$statusTimer,$benchmarkType,$crackerTypeId,[" . implode(",", $files) . "],$priority,$color,$isCpuOnly,$isSmall,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("PretaskTest:testCreatePretask($name,$cmd,$chunksize,$statusTimer,$benchmarkType,$crackerTypeId,[" . implode(",", $files) . "],$priority,$color,$isCpuOnly,$isSmall,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("PretaskTest:testCreatePretask($name,$cmd,$chunksize,$statusTimer,$benchmarkType,$crackerTypeId,[" . implode(",", $files) . "],$priority,$color,$isCpuOnly,$isSmall,$assert)");
    }
  }
  
  private function testGetPretask($pretaskId, $data, $files = [], $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "pretask",
      "request" => "getPretask",
      "pretaskId" => $pretaskId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("PretaskTest:testGetPretask([" . implode(", ", $data) . "],[" . implode(", ", $files) . "],$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("PretaskTest:testGetPretask([" . implode(", ", $data) . "],[" . implode(", ", $files) . "],$assert)", "Response does not match assert");
    }
    else {
      if (!$assert) {
        $this->testSuccess("PretaskTest:testGetPretask([" . implode(", ", $data) . "],[" . implode(", ", $files) . "],$assert)");
        return;
      }
      foreach ($data as $key => $val) {
        if (!array_key_exists($key, $response) || $val != $response[$key]) {
          $this->testFailed("PretaskTest:testGetPretask([" . implode(", ", $data) . "],[" . implode(", ", $files) . "],$assert)", "Response OK, but wrong content");
          return;
        }
      }
      foreach ($files as $val) {
        if (!in_array($val, $response['files'])) {
          $this->testFailed("PretaskTest:testGetPretask([" . implode(", ", $data) . "],[" . implode(", ", $files) . "],$assert)", "Response OK, but wrong files");
          return;
        }
      }
      $this->testSuccess("PretaskTest:testGetPretask([" . implode(", ", $data) . "],[" . implode(", ", $files) . "],$assert)");
    }
  }
  
  public function getTestName() {
    return "Pretask Test";
  }
}

HashtopolisTestFramework::register(new PretaskTest());