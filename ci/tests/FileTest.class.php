<?php

class FileTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType = HashtopolisTest::RUN_FAST;

  public function init($version){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing ".$this->getTestName()."...");
    parent::init($version);
  }

  public function run(){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running ".$this->getTestName()."...");
    $this->testListFilesEmpty();
    $this->testCreatingInlineFile();
    $this->testCreatedInlineFile();
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName()." completed");
  }

  private function testListFilesEmpty(){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "listFiles",
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("FileTest:testListFilesEmpty", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("FileTest:testListFilesEmpty", "Response not OK");
    }
    else if(!is_array($response['files'])){
      $this->testFailed("FileTest:testListFilesEmpty", "Expected array but got non-array");
    }
    else if(sizeof($response['files']) > 0){
      $this->testFailed("FileTest:testListFilesEmpty", "Expected empty array but got larger one");
    }
    else{
      $this->testSuccess("FileTest:testListFilesEmpty");
    }
  }

  private function testCreatingInlineFile(){
    $testFile = base64_encode("This is a test file content!");
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "addFile",
      "filename" => "test.txt",
      "fileType" => 0,
      "source" => "inline",
      "data" => $testFile,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("FileTest:testCreatingInlineFile", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("FileTest:testCreatingInlineFile", "Response not OK");
    }
  }

  private function testCreatedInlineFile(){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "listFiles",
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("FileTest:testCreatedInlineFile", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("FileTest:testCreatedInlineFile", "Response not OK");
    }
    else if(!is_array($response['files'])){
      $this->testFailed("FileTest:testCreatedInlineFile", "Expected array but got non-array");
    }
    else{
      $found = false;
      foreach($response['files'] as $file){
        if($file['filename'] == "test.txt"){
          $found = true;
        }
      }
      if(!$found){
        $this->testFailed("FileTest:testCreatedInlineFile", "Created file is not in list");
      }
      else{
        $this->testSuccess("FileTest:testCreatedInlineFile");
      }
    }
  }

  public function getTestName(){
    return "File Test";
  }
}

HashtopolisTestFramework::register(new FileTest());