<?php

class CrackerTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType = HashtopolisTest::RUN_FAST;

  protected $currentHashcat = "4.2.1";

  public function init($version){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing ".$this->getTestName()."...");
    parent::init($version);
  }

  public function run(){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running ".$this->getTestName()."...");
    $this->testListCrackers([1]);
    $this->testDeleteCracker(2, false); // invalid cracker type
    $this->testGetCracker(2, [], [], false); // invalid cracker
    $this->testGetCracker(1, [1], [1 => ['version' => $this->currentHashcat, 'downloadUrl' => 'https://hashcat.net/files/hashcat-'.$this->currentHashcat.'.7z', 'binaryBasename' => 'hashcat']]);
    $this->testCreateVersion(1, '1.2.3', 'hashcat', 'http://blub.com');
    $this->testCreateVersion(1, '1.2.3', 'hashcat', '', false); // empty url
    $this->testCreateVersion(1, '1.2.3', '', 'http://blub.com', false); // empty basename
    $this->testCreateVersion(1, '', 'hashcat', 'http://blub.com', false); // empty version
    $this->testCreateVersion(2, '1.2.3', 'hashcat', 'http://blub.com', false); // invalid cracker
    $this->testGetCracker(1, [1, 2], [
      1 => ['version' => $this->currentHashcat, 'downloadUrl' => 'https://hashcat.net/files/hashcat-'.$this->currentHashcat.'.7z', 'binaryBasename' => 'hashcat'],
      2 => ['version' => '1.2.3', 'downloadUrl' => 'http://blub.com', 'binaryBasename' => 'hashcat']]);
    $this->testDeleteVersion(5, false); // invalid version id
    $this->testDeleteVersion(1);
    $this->testGetCracker(1, [2], [2 => ['version' => '1.2.3', 'downloadUrl' => 'http://blub.com', 'binaryBasename' => 'hashcat']]);
    $this->testCreateCracker('', false); // empty name
    $this->testCreateCracker('My Own Cracker');
    $this->testListCrackers([1, 2]);
    $this->testGetCracker(2, [], []);
    $this->testCreateVersion(2, '1.2.3', 'own', 'http://blub.com');
    $this->testGetCracker(2, [3], [3 => ['version' => '1.2.3', 'downloadUrl' => 'http://blub.com', 'binaryBasename' => 'own']]);
    $this->testDeleteCracker(2);
    $this->testListCrackers([1]);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName()." completed");
  }

  private function testUpdateVersion($crackerVersionId, $version, $basename, $url, $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "createCracker",
      "crackerVersionId" => $crackerVersionId,
      "crackerBinaryVersion" => $version,
      "crackerBinaryBasename" => $basename,
      "crackerBinaryUrl" => $url,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("CrackerTest:testUpdateVersion($crackerVersionId,$version,$basename,$url,$assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("CrackerTest:testUpdateVersion($crackerVersionId,$version,$basename,$url,$assert)", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("CrackerTest:testUpdateVersion($crackerVersionId,$version,$basename,$url,$assert)", "Response OK, but expected to fail");
        return;
      }
      $this->testSuccess("CrackerTest:testUpdateVersion($crackerVersionId,$version,$basename,$url,$assert)");
    }
  }

  private function testCreateVersion($crackerTypeId, $version, $basename, $url, $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "createCracker",
      "crackerTypeId" => $crackerTypeId,
      "crackerBinaryVersion" => $version,
      "crackerBinaryBasename" => $basename,
      "crackerBinaryUrl" => $url,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("CrackerTest:testCreateVersion($crackerTypeId,$version,$basename,$url,$assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("CrackerTest:testCreateVersion($crackerTypeId,$version,$basename,$url,$assert)", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("CrackerTest:testCreateVersion($crackerTypeId,$version,$basename,$url,$assert)", "Response OK, but expected to fail");
        return;
      }
      $this->testSuccess("CrackerTest:testCreateVersion($crackerTypeId,$version,$basename,$url,$assert)");
    }
  }

  private function testCreateCracker($name, $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "createCracker",
      "name" => $name,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("CrackerTest:testCreateCracker($name,$assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("CrackerTest:testCreateCracker($name,$assert)", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("CrackerTest:testCreateCracker($name,$assert)", "Response OK, but expected to fail");
        return;
      }
      $this->testSuccess("CrackerTest:testCreateCracker($name,$assert)");
    }
  }

  private function testDeleteVersion($crackerVersionId, $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "deleteVersion",
      "crackerVersionId" => $crackerVersionId,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("CrackerTest:testDeleteVersion($crackerVersionId,$assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("CrackerTest:testDeleteVersion($crackerVersionId,$assert)", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("CrackerTest:testDeleteVersion($crackerVersionId,$assert)", "Response OK, but expected to fail");
        return;
      }
      $this->testSuccess("CrackerTest:testDeleteVersion($crackerVersionId,$assert)");
    }
  }

  private function testGetCracker($crackerTypeId, $versions, $versionData, $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "getCrackers",
      "crackerTypeId" => $crackerTypeId,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)", "Response OK, but expected to fail");
        return;
      }
      else if(sizeof($response['crackerVersions']) != sizeof($versions)){
        $this->testFailed("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)", "Response OK, but number of entries not matching");
        return;
      }
      foreach($response['crackers'] as $c){
        if(!in_array($c['versionId'], $versions)){
          $this->testFailed("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)", "Response OK, but wrong response");
          return;
        }
        foreach($versionData as $key => $val){
          if(!isset($c[$key]) || $c[$key] != $val){
            $this->testFailed("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)", "Response OK, but wrong version data");
            return;
          }
        }
      }
      $this->testSuccess("CrackerTest:testGetCracker($crackerTypeId,[" . implode(", ", $versions) . "],$assert)");
    }
  }

  private function testDeleteCracker($crackerTypeId, $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "deleteCracker",
      "crackerTypeId" => $crackerTypeId,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("CrackerTest:testDeleteCracker($crackerTypeId,$assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("CrackerTest:testDeleteCracker($crackerTypeId,$assert)", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("CrackerTest:testDeleteCracker($crackerTypeId,$assert)", "Response OK, but expected to fail");
        return;
      }
      $this->testSuccess("CrackerTest:testDeleteCracker($crackerTypeId,$assert)");
    }
  }

  private function testListCrackers($data, $assert = true){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "cracker",
      "request" => "listCrackers",
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)", "Empty response");
    }
    else if($response['response'] != 'OK' && $assert){
      $this->testFailed("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)", "Response not OK");
    }
    else{
      if(!$assert){
        $this->testFailed("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)", "Response OK, but expected to fail");
        return;
      }
      else if(sizeof($response['crackers']) != sizeof($data)){
        $this->testFailed("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)", "Response OK, but number of entries not matching");
        return;
      }
      foreach($response['crackers'] as $c){
        if(!in_array($c['crackerTypeId'], $data)){
          $this->testFailed("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)", "Response OK, but wrong response");
          return;
        }
      }
      $this->testSuccess("CrackerTest:testListCrackers([" . implode(", ", $data) . "],$assert)");
    }
  }

  public function getTestName(){
    return "Cracker Test";
  }
}

HashtopolisTestFramework::register(new CrackerTest());