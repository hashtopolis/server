<?php

/**
 * @deprecated
 */
class CrackerTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType    = HashtopolisTest::RUN_FAST;
  
  public function init($version) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing " . $this->getTestName() . "...");
    parent::init($version);
  }
  
  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    $this->testListCrackers([1]);
    $this->testDeleteCracker(2, false); // invalid cracker type
    $this->testGetCracker(2, [], [], false); // invalid cracker
    $this->testGetCracker(1, [1], [1 => ['binaryBasename' => 'hashcat']]);
    $this->testCreateVersion(1, '1.2.3', 'hashcat', 'http://blub.com');
    $this->testCreateVersion(1, '1.2.3', 'hashcat', '', false); // empty url
    $this->testCreateVersion(1, '1.2.3', '', 'http://blub.com', false); // empty basename
    $this->testCreateVersion(1, '', 'hashcat', 'http://blub.com', false); // empty version
    $this->testCreateVersion(2, '1.2.3', 'hashcat', 'http://blub.com', false); // invalid cracker
    $this->testGetCracker(1, [1, 2], [
        1 => ['binaryBasename' => 'hashcat'],
        2 => ['version' => '1.2.3', 'downloadUrl' => 'http://blub.com', 'binaryBasename' => 'hashcat']
      ]
    );
    $this->testDeleteVersion(5, false); // invalid version id
    $this->testDeleteVersion(1);
    $this->testGetCracker(1, [2], [2 => ['version' => '1.2.3', 'downloadUrl' => 'http://blub.com', 'binaryBasename' => 'hashcat']]);
    $this->testCreateCracker('', false); // empty name
    $this->testCreateCracker('My Own Cracker');
    $this->testListCrackers([1, 2]);
    $this->testGetCracker(2, [], []);
    $this->testCreateVersion(2, '1.2.3', 'own', 'http://blub.com');
    $this->testGetCracker(2, [3], [3 => ['version' => '1.2.3', 'downloadUrl' => 'http://blub.com', 'binaryBasename' => 'own']]);
    $this->testUpdateVersion(3, '2.3.4', 'my-own', '', false); // empty url
    $this->testUpdateVersion(3, '2.3.4', '', 'http://blah.com', false); // empty basename
    $this->testUpdateVersion(3, '', 'my-own', 'http://blah.com', false); // empty version
    $this->testUpdateVersion(5, '2.3.4', 'my-own', 'http://blah.com', false); // invalid version
    $this->testGetCracker(2, [3], [3 => ['version' => '1.2.3', 'downloadUrl' => 'http://blub.com', 'binaryBasename' => 'own']]);
    $this->testUpdateVersion(3, '2.3.4', 'my-own', 'http://blah.com');
    $this->testGetCracker(2, [3], [3 => ['version' => '2.3.4', 'downloadUrl' => 'http://blah.com', 'binaryBasename' => 'my-own']]);
    $this->testDeleteCracker(2);
    $this->testListCrackers([1]);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }
  
  private function testUpdateVersion($crackerVersionId, $version, $basename, $url, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "updateVersion",
      "crackerVersionId" => $crackerVersionId,
      "crackerBinaryVersion" => $version,
      "crackerBinaryBasename" => $basename,
      "crackerBinaryUrl" => $url,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("CrackerTest:testUpdateVersion($crackerVersionId,$version,$basename,$url,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("CrackerTest:testUpdateVersion($crackerVersionId,$version,$basename,$url,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("CrackerTest:testUpdateVersion($crackerVersionId,$version,$basename,$url,$assert)");
    }
  }
  
  private function testCreateVersion($crackerTypeId, $version, $basename, $url, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "addVersion",
      "crackerTypeId" => $crackerTypeId,
      "crackerBinaryVersion" => $version,
      "crackerBinaryBasename" => $basename,
      "crackerBinaryUrl" => $url,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("CrackerTest:testCreateVersion($crackerTypeId,$version,$basename,$url,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("CrackerTest:testCreateVersion($crackerTypeId,$version,$basename,$url,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("CrackerTest:testCreateVersion($crackerTypeId,$version,$basename,$url,$assert)");
    }
  }
  
  private function testCreateCracker($name, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "createCracker",
      "crackerName" => $name,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("CrackerTest:testCreateCracker($name,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("CrackerTest:testCreateCracker($name,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("CrackerTest:testCreateCracker($name,$assert)");
    }
  }
  
  private function testDeleteVersion($crackerVersionId, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "deleteVersion",
      "crackerVersionId" => $crackerVersionId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("CrackerTest:testDeleteVersion($crackerVersionId,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("CrackerTest:testDeleteVersion($crackerVersionId,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("CrackerTest:testDeleteVersion($crackerVersionId,$assert)");
    }
  }
  
  private function testGetCracker($crackerTypeId, $versions, $versionData, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "getCracker",
      "crackerTypeId" => $crackerTypeId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)", "Response does not match assert");
    }
    else {
      if (!$assert) {
        $this->testSuccess("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)");
        return;
      }
      else if (sizeof($response['crackerVersions']) != sizeof($versions)) {
        $this->testFailed("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)", "Response OK, but number of entries not matching");
        return;
      }
      foreach ($response['crackerVersions'] as $c) {
        if (!in_array($c['versionId'], $versions)) {
          $this->testFailed("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)", "Response OK, but wrong response");
          return;
        }
        foreach ($versionData[$c['versionId']] as $key => $val) {
          if (!isset($c[$key]) || $c[$key] != $val) {
            $this->testFailed("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)", "Response OK, but wrong version data on $key");
            return;
          }
        }
      }
      $this->testSuccess("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)");
    }
  }
  
  private function testDeleteCracker($crackerTypeId, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "deleteCracker",
      "crackerTypeId" => $crackerTypeId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("CrackerTest:testDeleteCracker($crackerTypeId,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("CrackerTest:testDeleteCracker($crackerTypeId,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("CrackerTest:testDeleteCracker($crackerTypeId,$assert)");
    }
  }
  
  private function testListCrackers($data, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "listCrackers",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)", "Response does not match assert");
    }
    else {
      if (!$assert) {
        $this->testSuccess("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)");
        return;
      }
      else if (sizeof($response['crackers']) != sizeof($data)) {
        $this->testFailed("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)", "Response OK, but number of entries not matching");
        return;
      }
      foreach ($response['crackers'] as $c) {
        if (!in_array($c['crackerTypeId'], $data)) {
          $this->testFailed("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)", "Response OK, but wrong response");
          return;
        }
      }
      $this->testSuccess("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)");
    }
  }
  
  public function getTestName() {
    return "Cracker Test";
  }
}

HashtopolisTestFramework::register(new CrackerTest());