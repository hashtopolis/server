<?php

use DBA\Agent;
use DBA\Benchmark;
use DBA\Factory;

 class BenchmarkTest extends HashtopolisTest {
  protected $minVersion = "0.13.0";
  protected $maxVersion = "master";
  protected $runType    = HashtopolisTest::RUN_FAST;

  private  $agent = null;
  
  public function init($version) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing " . $this->getTestName() . "...");
    parent::init($version);
  }
  
  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    $this->createAgentWithHardwareGroup();
    $this->testAddToCache();
    $this->testGetFromCache();
    $this->testDeleteCache();
    $this->testTtl();
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }

  public function getTestName() {
    return "Benchmark Test";
  }

  private function createAgentWithHardwareGroup() {
    $agent = new Agent(100, "testAgent", "ebfc57ec-2d6f-4a60-932d-60f127dbb2a8",0, null, "",  0, 1, 0, "TeStToKeN0", "sendProgress",
      1683904809, "127.0.0.1", 1, 0, "s3-python-0.7.1");
    $agentSameHardware = new Agent(101, "testAgent2", "ebfc57ec-2d6f-4a60-932d-60f127dbb2a9",0, null, "",  0, 1, 0, "TeStToKeN1", "sendProgress",
      1683904809, "127.0.0.2", 1, 0, "s3-python-0.7.1");
    $agentDifferentHardware = new Agent(102, "testAgent3", "ebfc57ec-2d6f-4a60-932d-60f127dbb2b8",0, null, "",  0, 1, 0, "TeStToKeN2", "sendProgress",
      1683904809, "127.0.0.3", 1, 0, "s3-python-0.7.1");
    
    Factory::getAgentFactory()->save($agent);
    Factory::getAgentFactory()->save($agentSameHardware);
    Factory::getAgentFactory()->save($agentDifferentHardware);

    $agent = HardwareGroupUtils::updateHardwareOfAgent("11th Gen Intel(R) Core(TM) i7-1165G7 @ 2.80GHz", $agent);
    $agentSameHardware = HardwareGroupUtils::updateHardwareOfAgent("11th Gen Intel(R) Core(TM) i7-1165G7 @ 2.80GHz", $agentSameHardware);
    $agentDifferentHardware = HardwareGroupUtils::updateHardwareOfAgent("10th Gen Intel(R) Core(TM) i6-1165G7 @ 2.80GHz", $agentDifferentHardware);

    if ($agent->getHardwareGroupId() != $agentSameHardware->getHardwareGroupId()) {
      $this->testFailed("BenchmarkTest:createAgentWithHardwareGroup", "Agents with the same hardware are not added to the same hardwareGroup!
       Agent1 hardwaregroup id: " . $agent->getHardwareGroupId() . "Agent2 hardwareGroupId: " . $agentSameHardware->getHardwareGroupId());
    }
    if ($agent->getHardwareGroupId() == $agentDifferentHardware->getHardwareGroupId()) {
      $this->testFailed("BenchmarkTest:createAgentWithHardwareGroup", "Agents with different hardware are added to the same hardwareGroup!
      Agent1 hardwaregroup id: " . $agent->getHardwareGroupId() . "Agent2 hardwareGroupId: " . $agentDifferentHardware->getHardwareGroupId());
    }

    $this->agent = $agent; //save agent for future tests
    $this->testSuccess("BenchmarkTest:createAgentWithHardwareGroup");
  }

  private function testAddToCache(){

    $benchmark = BenchmarkUtils::saveBenchmarkInCache("#HL# -a 3 ?l?l?l?l -d 1 --force", $this->agent->getHardwareGroupId(), "676:1.78", 1000, "speed", 1);

    if(!isset($benchmark)) {
      $this->testFailed("BenchmarkTest:testAddToCache", "Cannot add benchmark to cache");
    } else {
      $this->testSuccess("BenchmarkTest:testAddToCache");
    }
  }

  private function testGetFromCache(){
    $benchmark = BenchmarkUtils::getBenchmarkByValue("#HL# -a 3 ?l?l?l?l -d 1 --force", $this->agent->getHardwareGroupId(), 1000, 1, 1);

    if(!isset($benchmark)) {
      $this->testFailed("BenchmarkTest:testGetFromCache", "Cannot get benchmark from cache in normal situation");
    } else {
      $this->testSuccess("BenchmarkTest:testGetFromCache");
    } 
    
    $benchmark2 = BenchmarkUtils::getBenchmarkByValue("#HL# -a3     ?l?l?l?l -d 1 --force", $this->agent->getHardwareGroupId(),1000, 1, 1);
    $benchmark3 = BenchmarkUtils::getBenchmarkByValue("#HL# -d 1 --attack-mode 3 ?l?l?l?l --force", $this->agent->getHardwareGroupId(),1000, 1, 1);
    $benchmark4 = BenchmarkUtils::getBenchmarkByValue("#HL# --force -a3       ?l?l?l?l -d      1", $this->agent->getHardwareGroupId(), 1000, 1, 1);

    if(!isset($benchmark2) || !isset($benchmark3) || !isset($benchmark4)) {
      $this->testFailed("BenchmarkTest:testGetFromCache", "Cannot get benchmark from cache with parsing commandline in different formats");
    } else {
      $this->testSuccess("BenchmarkTest:testGetFromCache");
    }
  }

  private function testDeleteCache() {
    BenchmarkUtils::deleteCache();
    $benchmark = BenchmarkUtils::getBenchmarkByValue(1000, "#HL# -a 3 ?l?l?l?l -d 1 --force", 1, "1", 0);
    if(isset($benchmark)) {
      $this->testFailed("BenchmarkTest:testDeleteCache", "There is still a value in the cache!");
    } else {
      $this->testSuccess("BenchmarkTest:testDeleteCache");
    }   
  }
  
  private function testTtl() {
    $benchmark = new Benchmark(3, "speed", "1234:88","#HL# -a 3 ?u?u?u", 200, 1, time() - 10, 1); //ttl in the past to test invalid ttl
    Factory::getBenchmarkFactory()->save($benchmark);
    $found = BenchmarkUtils::getBenchmarkByValue("#HL# -a 3 ?u?u?u", 1, 200, 1, 1);
    if($found != null) {
      $this->testFailed("BenchmarkTest:testTtl", "benchmark with ttl in the past should not be valid!");
    } else {
      $this->testSuccess("BenchmarkTest:testTtl");
    }
  }
}

HashtopolisTestFramework::register(new BenchmarkTest());
