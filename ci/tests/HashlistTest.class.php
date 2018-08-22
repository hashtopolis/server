<?php

class HashlistTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType = HashtopolisTest::RUN_FAST;

  private $token = "";

  public function init($version){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing ".$this->getTestName()."...");
    parent::init($version);
  }

  public function run(){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running ".$this->getTestName()."...");
    $this->testListHashlists();
    $this->testHashlistCreate(0);
    $this->testHashlistCreate(1);
    $this->testHashlistCreate(2);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName()." completed");
  }

  private function testHashlistCreate($type = 0){
    $data = "";
    $hashtype = -1;
    switch($type){
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
		  "name" => "API Hashlist",
		  "isSalted" => false,
		  "isSecret" => false,
		  "isHexSalt" => false,
		  "separator" => ":",
		  "format" => $type,
		  "hashtypeId" => $hashtype,
		  "accessGroupId" => 1,
      "data" => $data,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("HashlistTest:testHashlistCreate($type)", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("HashlistTest:testHashlistCreate($type)", "Response not OK");
    }
    else{
      $this->testSuccess("HashlistTest:testHashlistCreate($type)");
    }
  }

  private function testListHashlists($assert = []){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "hashlist",
      "request" => "listHashlists",
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("HashlistTest:testListHashlists(" . implode(", ", $assert) . ")", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("HashlistTest:testListHashlists(" . implode(", ", $assert) . ")", "Response not OK");
    }
    else{
      $this->testSuccess("HashlistTest:testListHashlists(" . implode(", ", $assert) . ")");
    }
  }

  public function getTestName(){
    return "Hashlist Test";
  }
}

HashtopolisTestFramework::register(new HashlistTest());