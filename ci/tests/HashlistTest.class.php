<?php

/**
 * @deprecated
 */
class HashlistTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType    = HashtopolisTest::RUN_FAST;
  
  private $token = "";
  
  public function init($version) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing " . $this->getTestName() . "...");
    parent::init($version);
  }
  
  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    $this->testListHashlists();
    $this->testHashlistCreate(0);
    $this->testHashlistCreate(1);
    $this->testHashlistCreate(2);
    $this->testListHashlists(['Hashlist 0', 'Hashlist 1', 'Hashlist 2']);
    $this->testGetHashlist(1, ['name' => 'Hashlist 0', 'hashtypeId' => 0, 'format' => 0, 'hashCount' => 10, 'cracked' => 0, 'isSecret' => false, 'saltSeparator' => ':']);
    $this->testGetHashlist(2, ['name' => 'Hashlist 1', 'hashtypeId' => 2500, 'format' => 1, 'hashCount' => 1, 'cracked' => 0, 'isSecret' => false, 'saltSeparator' => ':']);
    $this->testGetHashlist(3, ['name' => 'Hashlist 2', 'hashtypeId' => 6211, 'format' => 2, 'hashCount' => 1, 'cracked' => 0, 'isSecret' => false, 'saltSeparator' => ':']);
    $this->testImportCracked();
    $this->testGetHashlist(1, ['name' => 'Hashlist 0', 'hashtypeId' => 0, 'format' => 0, 'hashCount' => 10, 'cracked' => 3, 'isSecret' => false, 'saltSeparator' => ':']);
    $this->testGetHash("0028080e7fa8c81268ef340d7d692681", "found1");
    $this->testGetHash("00112233445566778899aabbccddeeff", false);
    $this->testDeleteHashlist(3);
    $this->testListHashlists(['Hashlist 0', 'Hashlist 1']);
    $this->testArchiveHashlist(1);
    $this->testDeleteHashlist(1);
    $this->testListHashlists(['Hashlist 1']);
    // the following tests are used to verify that deleting the last hash doesn't result in an error
    $this->testDeleteHashlist(2);
    $this->testHashlistCreate(0);
    $this->testDeleteHashlist(4);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }
  
  private function testDeleteHashlist($hashlistId) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "hashlist",
      "request" => "deleteHashlist",
      "hashlistId" => $hashlistId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("HashlistTest:testDeleteHashlist($hashlistId)", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("HashlistTest:testDeleteHashlist($hashlistId)", "Response not OK");
    }
    else {
      $this->testSuccess("HashlistTest:testDeleteHashlist($hashlistId)");
    }
  }
  
  private function testArchiveHashlist($hashlistId) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "hashlist",
      "request" => "setArchived",
      "isArchived" => true,
      "hashlistId" => $hashlistId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("HashlistTest:testArchiveHashlist($hashlistId)", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("HashlistTest:testArchiveHashlist($hashlistId)", "Response not OK");
    }
    else {
      $this->testSuccess("HashlistTest:testArchiveHashlist($hashlistId)");
    }
  }
  
  private function testGetHash($hash, $assert) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "hashlist",
      "request" => "getHash",
      "hash" => $hash,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("HashlistTest:testGetHash($hash,$assert)", "Empty response");
    }
    else if ($assert && $response['response'] != 'OK') {
      $this->testFailed("HashlistTest:testGetHash($hash,$assert)", "Response not OK");
    }
    else if (!$assert && $response['response'] != 'ERROR') {
      $this->testFailed("HashlistTest:testGetHash($hash,$assert)", "Response not ERROR");
    }
    else {
      if ($assert && $assert != $response['plain']) {
        $this->testFailed("HashlistTest:testGetHash($hash,$assert)", "Plain is not correct for hash");
        return;
      }
      $this->testSuccess("HashlistTest:testGetHash($hash,$assert)");
    }
  }
  
  private function testImportCracked() {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "hashlist",
      "request" => "importCracked",
      "hashlistId" => 1,
      "separator" => ":",
      // sending 3 founds of the hashlist
      "data" => "MDAyODA4MGU3ZmE4YzgxMjY4ZWYzNDBkN2Q2OTI2ODE6Zm91bmQxCjAwMmU5NWQ4MmJlMzAzOTZmY2NkMzc1ZmYyM2Y4YjRjOmZvdW5kMgowMDM0YzVlNDE4YWU0ZjJlYmE1OTBhMTY2OTZlZGJiMzpmb3VuZDM=",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("HashlistTest:testImportCracked", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("HashlistTest:testImportCracked", "Response not OK");
    }
    else if ($response['linesProcessed'] != 3) {
      $this->testFailed("HashlistTest:testImportCracked", "Not matching number of processed lines");
    }
    else if ($response['newCracked'] != 3) {
      $this->testFailed("HashlistTest:testImportCracked", "Not matching number of new cracked lines");
    }
    else if ($response['notFound'] != 0) {
      $this->testFailed("HashlistTest:testImportCracked", "Not matching number of not found lines");
    }
    else {
      $this->testSuccess("HashlistTest:testImportCracked");
    }
  }
  
  private function testGetHashlist($hashlistId, $assert = []) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "hashlist",
      "request" => "getHashlist",
      "hashlistId" => $hashlistId,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("HashlistTest:testGetHashlist($hashlistId," . implode(",", $assert) . ")", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("HashlistTest:testGetHashlist($hashlistId," . implode(",", $assert) . ")", "Response not OK");
    }
    else {
      foreach ($assert as $key => $value) {
        if (!isset($response[$key])) {
          $this->testFailed("HashlistTest:testGetHashlist($hashlistId," . implode(",", $assert) . ")", "$key not in response but in assert");
          return;
        }
        else if ($response[$key] != $value) {
          $this->testFailed("HashlistTest:testGetHashlist($hashlistId," . implode(",", $assert) . ")", "Value from key ($key) not in response but in assert");
          return;
        }
      }
      $this->testSuccess("HashlistTest:testGetHashlist($hashlistId," . implode(",", $assert) . ")");
    }
  }
  
  public function testHashlistCreate($type = 0) {
    $data = "";
    $hashtype = -1;
    switch ($type) {
      case 0: // 10 MD5 hashes
        $data = "MDAwNDA1ZGJjMDdjM2I1OTVmYzg3MDMxYWY2Zjk4NzkKMDAxZTk5YmQ2OWYwYTU4MmQzOWNjYTcyODRiNjA3ODQKMDAyMWNhNTIwNDljNzM0YWMwZDNkNmY5MjA0MmFiZjcKMDAyODA4MGU3ZmE4YzgxMjY4ZWYzNDBkN2Q2OTI2ODEKMDAyYWNlMzY1YTM0MWU1NWRlOWQ2Mzg3MTAwYjJjNjUKMDAyZTk1ZDgyYmUzMDM5NmZjY2QzNzVmZjIzZjhiNGMKMDAzNGM1ZTQxOGFlNGYyZWJhNTkwYTE2Njk2ZWRiYjMKMDAzYmIzYmVhZmJkODY2NzE2M2UxOTI5OTQzM2RmODMKMDA0MjhkOTRkOTQ4MmQ4YzcwMzdiNjg2NTUyMWIzZmQKMDA0YTAxOWM3ZGEwNGYzZDI0ODg1YmFkOTg0YjRhNDM=";
        $hashtype = 0;
        break;
      case 2: // truecrypt hash
        $data = "h5FJZ/FHN6Z/tGDye4rrgd4rQb8nQLPdeHhOAnY5UdqkfHyiNedcIuyNlZ1rZ/fu3vrWHmoNA4B503IajnIV5BVnHox7Pb7WRToRTm24mlK+mpwWmKnGmPHjf4DXr68O+6grbl9d8yvSiblTQ8Z3Xix/Al7x2L+uhAQqklRuFbY1tfreOu9u5Sp6WrAY0z6pi8EV38Yq9gYYf7q4y9puhBdALHIsqMKwfmymozv5SyziqBmp+M+qWvcOOvblNQ06MG8DbxP/W6l9VyjV9kE7SCx09SghGud7bBaSFcVIfVo84jc2sWmWuGxxsS0SDfKO8yL1FD2aJY0K56qowZOm3LW/GOPFe1R00kuEP43U6Dp0EJOW3bTwxQ02V6fqzIgoVo5RIC3kjNLf5ay+PYhAreHORLcW1cAAjyshuZgTU8sSuK8lkqWrdEroNiM0n1UazzccgfhtF6hCJlSYnweBebI4biqoN1hToYAs2LxdQc5FeV94uA5p/Po9FM+RJ8OjP6Lcdq1zlg+3vOFd1Ingtuynvu03M4h81ebzk5oBXU1EkYUGCy87utRuRtQXuPCDDpHt1evBfNWpkxZ5Kjav2D+h7cVdolUYyOf/YeIBl2+ixfyZaeBcvuDc56Dvh2tzQLvok3ydnIJI8ODq5wX+fh0tpIkC9PPifSz1MrcCHhg=";
        $hashtype = 6211;
        break;
      case 1: // hccapx file
        $data = "SENQWAQAAAAAGTgzODE1MzM0MDYwMDM4MDc2ODU4ODE1MjMAAAAAAAAAAd04C9VLycMW3OMVYsIsh9Gu9Q8igBz68ZKyBdR7gfQ/kfhQyBl22gGeAHIvOVg3BpKrBWL3C5h73Pn5UB4z8+yjofIhalK2DIcZHnRzrFTssCOsWYm+zx48fkUJewABAwB3/gEJACAAAAAAAAAAAfrxkrIF1HuB9D+R+FDIGXbaAZ4Aci85WDcGkqsFYvcLAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABjdFgBQ8gEBAABQ8gIBAABQ8gIBAABQ8gIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
        $hashtype = 2500;
        break;
    }
    $response = HashtopolisTestFramework::doRequest([
      "section" => "hashlist",
      "request" => "createHashlist",
      "name" => "Hashlist $type",
      "isSalted" => false,
      "isSecret" => false,
      "isHexSalt" => false,
      "separator" => ":",
      "format" => $type,
      "hashtypeId" => $hashtype,
      "accessGroupId" => 1,
      "data" => $data,
      "useBrain" => false,
      "brainFeatures" => 0,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("HashlistTest:testHashlistCreate($type)", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("HashlistTest:testHashlistCreate($type)", "Response not OK");
    }
    else {
      $this->testSuccess("HashlistTest:testHashlistCreate($type)");
    }
  }
  
  private function testListHashlists($assert = []) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "hashlist",
      "request" => "listHashlists",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("HashlistTest:testListHashlists(" . implode(", ", $assert) . ")", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("HashlistTest:testListHashlists(" . implode(", ", $assert) . ")", "Response not OK");
    }
    else if (sizeof($assert) != sizeof($response['hashlists'])) {
      $this->testFailed("HashlistTest:testListHashlists(" . implode(", ", $assert) . ")", "Not matching number of hashlists");
    }
    else {
      foreach ($response['hashlists'] as $hashlist) {
        if (!in_array($hashlist['name'], $assert)) {
          $this->testFailed("HashlistTest:testListHashlists(" . implode(", ", $assert) . ")", "Not matching hashlist name");
          return;
        }
      }
      $this->testSuccess("HashlistTest:testListHashlists(" . implode(", ", $assert) . ")");
    }
  }
  
  public function getTestName() {
    return "Hashlist Test";
  }
}

HashtopolisTestFramework::register(new HashlistTest());
