<?php

/**
 * @deprecated
 */
class FileTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType    = HashtopolisTest::RUN_FAST;
  
  public function init($version) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing " . $this->getTestName() . "...");
    parent::init($version);
  }
  
  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    $this->testListFilesEmpty();
    $this->testCreatingInlineFile();
    $this->testCreatedInlineFile();
    $this->testCreatingFileTwice();
    $this->testGetFile();
    $this->testFileDownload();
    $this->testSecret();
    $this->testGetFile(false, 0);
    $this->testFileType();
    $this->testGetFile(false, 2);
    $this->testDelete();
    $this->testListFilesEmpty();
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }
  
  private function testDelete() {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "deleteFile",
      "fileId" => 1,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("FileTest:testDelete", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("FileTest:testDelete", "Response not OK");
    }
    else {
      $this->testSuccess("FileTest:testDelete");
    }
  }
  
  private function testFileType() {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "setFileType",
      "fileId" => 1,
      "fileType" => 2,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("FileTest:testFileType", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("FileTest:testFileType", "Response not OK");
    }
    else {
      $this->testSuccess("FileTest:testFileType");
    }
  }
  
  private function testSecret() {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "setSecret",
      "fileId" => 1,
      "isSecret" => false,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("FileTest:testSecret", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("FileTest:testSecret", "Response not OK");
    }
    else {
      $this->testSuccess("FileTest:testSecret");
    }
  }
  
  private function testListFilesEmpty() {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "listFiles",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("FileTest:testListFilesEmpty", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("FileTest:testListFilesEmpty", "Response not OK");
    }
    else if (!is_array($response['files'])) {
      $this->testFailed("FileTest:testListFilesEmpty", "Expected array but got non-array");
    }
    else if (sizeof($response['files']) > 0) {
      $this->testFailed("FileTest:testListFilesEmpty", "Expected empty array but got larger one");
    }
    else {
      $this->testSuccess("FileTest:testListFilesEmpty");
    }
  }
  
  private function testCreatingInlineFile() {
    $response = $this->fileCreation();
    if ($response === false) {
      $this->testFailed("FileTest:testCreatingInlineFile", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("FileTest:testCreatingInlineFile", "Response not OK");
    }
    else {
      $this->testSuccess("FileTest:testCreatingInlineFile");
    }
  }
  
  private function testFileDownload() {
    $testContent = "This is a test file content!";
    $url = 'http://localhost/getFile.php?file=1&apiKey=mykey';
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => TRUE
      )
    );
    $response = curl_exec($ch);
    if ($response != $testContent) {
      HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_ERROR, $response);
      $this->testFailed("FileTest:testFileDownload", "File content does not match!");
    }
    else {
      $this->testSuccess("FileTest:testFileDownload");
    }
  }
  
  private function testGetFile($secret = true, $fileType = 0) {
    $testContent = "This is a test file content!";
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "getFile",
      "fileId" => 1,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("FileTest:testGetFile($secret,$fileType)", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("FileTest:testGetFile($secret,$fileType)", "Response not OK");
    }
    else if ($response['size'] != strlen($testContent)) {
      $this->testFailed("FileTest:testGetFile($secret,$fileType)", "File size not matching");
    }
    else if ($response['url'] != 'getFile.php?file=1&apiKey=mykey') {
      $this->testFailed("FileTest:testGetFile($secret,$fileType)", "Download url not correct");
    }
    else if ($response['fileType'] != $fileType) {
      $this->testFailed("FileTest:testGetFile($secret,$fileType)", "File type not correct");
    }
    else if ($response['filename'] != 'test.txt') {
      $this->testFailed("FileTest:testGetFile($secret,$fileType)", "Filename not matching");
    }
    else if ($response['isSecret'] != $secret) {
      $this->testFailed("FileTest:testGetFile($secret,$fileType)", "Wrong isSecret value of file");
    }
    else {
      $this->testSuccess("FileTest:testGetFile($secret,$fileType)");
    }
  }
  
  public function testCreateFile($filename, $filetype, $data, $assert = true) {
    $testFile = base64_encode("This is a test file content!");
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "addFile",
      "filename" => "test.txt",
      "fileType" => 0,
      "source" => "inline",
      "data" => $testFile,
      "accessGroupId" => 1,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("FileTest:testCreateFile($filename,$filetype,$assert)", "Empty response");
    }
    else if ($response['response'] != 'OK' && $assert) {
      $this->testFailed("FileTest:testCreateFile($filename,$filetype,$assert)", "Response not OK");
    }
    else {
      if (!$assert) {
        $this->testFailed("FileTest:testCreateFile($filename,$filetype,$assert)", "Response OK, but expected to fail");
        return;
      }
      $this->testSuccess("FileTest:testCreateFile($filename,$filetype,$assert)");
    }
  }
  
  private function fileCreation() {
    $testFile = base64_encode("This is a test file content!");
    return HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "addFile",
      "filename" => "test.txt",
      "fileType" => 0,
      "source" => "inline",
      "data" => $testFile,
      "accessGroupId" => 1,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
  }
  
  private function testCreatingFileTwice() {
    $response = $this->fileCreation();
    if ($response === false) {
      $this->testFailed("FileTest:testCreatingFileTwice", "Empty response");
    }
    else if ($response['response'] != 'ERROR') {
      $this->testFailed("FileTest:testCreatingFileTwice", "Response not ERROR");
    }
    else {
      $this->testSuccess("FileTest:testCreatingFileTwice");
    }
  }
  
  private function testCreatedInlineFile() {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "listFiles",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("FileTest:testCreatedInlineFile", "Empty response");
    }
    else if ($response['response'] != 'OK') {
      $this->testFailed("FileTest:testCreatedInlineFile", "Response not OK");
    }
    else if (!is_array($response['files'])) {
      $this->testFailed("FileTest:testCreatedInlineFile", "Expected array but got non-array");
    }
    else {
      $found = false;
      foreach ($response['files'] as $file) {
        if ($file['filename'] == "test.txt") {
          if ($file['fileType'] != 0) {
            $this->testFailed("FileTest:testCreatedInlineFile", "Created file does not have same fileType");
          }
          $found = true;
        }
      }
      if (!$found) {
        $this->testFailed("FileTest:testCreatedInlineFile", "Created file is not in list");
      }
      else {
        $this->testSuccess("FileTest:testCreatedInlineFile");
      }
    }
  }
  
  public function getTestName() {
    return "File Test";
  }
}

HashtopolisTestFramework::register(new FileTest());