<?php

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
    $this->testCreatePretask("Pretask fail", "#HL# -a 3 ?l?l?l?l?l?l", false, 0); // chunk size 0
    $this->testCreatePretask("Pretask fail", "#HL# -a 3 ?l?l?l?l?l?l", false, -600); // chunk size negative
    $this->testCreatePretask("Pretask fail", "#HL# -a 3 ?l?l?l?l?l?l", false, 600, 5, 'speed', 2); // invalid cracker type
    $this->testSetPretaskPriority(2, 5, false); // invalid pretask
    $this->testSetPretaskPriority(1, "bla", false); // invalid priority
    $this->testSetPretaskPriority(1, 5);
    $this->testGetPretask(1, ['pretaskId' => 1, 'name' => "Pretask #1", 'attackCmd' => "#HL# -a 3 ?l?l?l?l?l?l", 'priority' => 5]);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
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
      $this->testFailed("AccountTest:testChangePassword($pretaskId,$priority,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("AccountTest:testChangePassword($pretaskId,$priority,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("AccountTest:testChangePassword($pretaskId,$priority,$assert)");
    }
  }

  private function testListPretasks($data, $assert = true){
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
        foreach($data[$pretask['pretaskId']] as $key => $val){
          if (!isset($pretask[$key]) || $val != $pretask[$key]) {
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
        if (!isset($response[$key]) || $val != $response[$key]) {
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