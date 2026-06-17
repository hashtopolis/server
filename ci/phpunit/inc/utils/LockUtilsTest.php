<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class LockUtilsTest extends TestBase {
  private const TEST_LOCK = 'phpunit_test.lock';
  private const LOCK_DIR  = __DIR__ . '/../../../../src/inc/utils/locks';
  
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $this->releaseTestLock();
    $this->cleanupLockFiles();
    $this->lockFile = self::LOCK_DIR . '/' . self::TEST_LOCK;
  }
  
  #[Override]
  protected function tearDown(): void {
    $this->releaseTestLock();
    $this->cleanupLockFiles();
    parent::tearDown();
  }
  
  private function releaseTestLock(): void {
    LockUtils::release(self::TEST_LOCK);
  }
  
  private function cleanupLockFiles(): void {
    $prefixes = [Lock::CHUNKING, self::TEST_LOCK];
    foreach ($prefixes as $prefix) {
      $path = self::LOCK_DIR . '/' . $prefix;
      if (is_file($path)) {
        unlink($path);
      }
    }
  }
  
  public function testGetCreatesAndAcquiresLock(): void {
    LockUtils::get(self::TEST_LOCK);
    $this->assertFileExists($this->lockFile);
    LockUtils::release(self::TEST_LOCK);
  }
  
  public function testGetReturnsCachedInstance(): void {
    LockUtils::get(self::TEST_LOCK);
    LockUtils::get(self::TEST_LOCK);
    LockUtils::release(self::TEST_LOCK);
    $this->assertFileExists($this->lockFile);
  }
  
  public function testReleaseReleasesLockForReacquisition(): void {
    LockUtils::get(self::TEST_LOCK);
    LockUtils::release(self::TEST_LOCK);
    
    LockUtils::get(self::TEST_LOCK);
    LockUtils::release(self::TEST_LOCK);
    $this->assertFileExists($this->lockFile);
  }
  
  public function testReleaseIsNoopForUnknownLock(): void {
    LockUtils::release('nonexistent.lock');
    $this->assertFileDoesNotExist(self::LOCK_DIR . '/nonexistent.lock');
  }
  
  public function testDeleteLockFileRemovesExistingLockFile(): void {
    $taskId = 999001;
    $lockFilePath = self::LOCK_DIR . '/' . Lock::CHUNKING . $taskId;
    
    touch($lockFilePath);
    $this->assertFileExists($lockFilePath);
    
    LockUtils::deleteLockFile($taskId);
    
    $this->assertFileDoesNotExist($lockFilePath);
  }
  
  public function testDeleteLockFileDoesNotThrowForMissingFile(): void {
    $taskId = 999002;
    LockUtils::deleteLockFile($taskId);
    $lockFilePath = self::LOCK_DIR . '/' . Lock::CHUNKING . $taskId;
    $this->assertFileExists($lockFilePath);
  }
  
  public function testDeleteLockFileCleansUpOnlySpecifiedTask(): void {
    $taskIdA = 999003;
    $taskIdB = 999004;
    $pathA = self::LOCK_DIR . '/' . Lock::CHUNKING . $taskIdA;
    $pathB = self::LOCK_DIR . '/' . Lock::CHUNKING . $taskIdB;
    
    touch($pathA);
    touch($pathB);
    
    LockUtils::deleteLockFile($taskIdA);
    
    $this->assertFileDoesNotExist($pathA);
    $this->assertFileExists($pathB);
    
    unlink($pathB);
  }
}
