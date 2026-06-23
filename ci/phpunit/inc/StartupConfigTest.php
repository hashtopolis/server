<?php

namespace Hashtopolis\inc;

use Hashtopolis\TestBase;
use ReflectionClass;
use ReflectionException;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class StartupConfigTest extends TestBase {
  /**
   * @throws ReflectionException
   */
  protected function tearDown(): void {
    $p = (new ReflectionClass(StartupConfig::class))->getProperty('instance');
    $p->setValue(null, null);
    parent::tearDown();
  }

  /**
   * getInstance returns a StartupConfig object.
   */
  public function testGetInstanceReturnsStartupConfig(): void {
    $this->assertInstanceOf(StartupConfig::class, StartupConfig::getInstance());
  }

  /**
   * getInstance returns the same instance on consecutive calls (singleton).
   */
  public function testGetInstanceIsSingleton(): void {
    $i1 = StartupConfig::getInstance();
    $i2 = StartupConfig::getInstance();
    $this->assertSame($i1, $i2);
  }

  /**
   * getInstance(true) forces creation of a new instance.
   */
  public function testGetInstanceWithForceCreatesNewInstance(): void {
    $i1 = StartupConfig::getInstance();
    $i2 = StartupConfig::getInstance(true);
    $this->assertNotSame($i1, $i2);
  }

  /**
   * reload() forces creation of a new instance via getInstance(true).
   */
  public function testReloadCreatesNewInstance(): void {
    $i1 = StartupConfig::getInstance();
    StartupConfig::reload();
    $i2 = StartupConfig::getInstance();
    $this->assertNotSame($i1, $i2);
  }

  /**
   * getDirectories returns an array with all five expected keys.
   */
  public function testGetDirectoriesReturnsArray(): void {
    $dirs = StartupConfig::getInstance()->getDirectories();
    $this->assertIsArray($dirs);
    $this->assertArrayHasKey('files', $dirs);
    $this->assertArrayHasKey('import', $dirs);
    $this->assertArrayHasKey('log', $dirs);
    $this->assertArrayHasKey('config', $dirs);
    $this->assertArrayHasKey('tus', $dirs);
  }

  /**
   * getDirectoryFiles returns a string path.
   */
  public function testGetDirectoryFilesReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getDirectoryFiles());
  }

  /**
   * getDirectoryImport returns a string path.
   */
  public function testGetDirectoryImportReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getDirectoryImport());
  }

  /**
   * getDirectoryLog returns a string path.
   */
  public function testGetDirectoryLogReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getDirectoryLog());
  }

  /**
   * getDirectoryConfig returns a string path.
   */
  public function testGetDirectoryConfigReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getDirectoryConfig());
  }

  /**
   * getDirectoryTus returns a string path.
   */
  public function testGetDirectoryTusReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getDirectoryTus());
  }

  /**
   * getDatabaseType returns a string (empty by default or set from env).
   */
  public function testGetDatabaseTypeReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getDatabaseType());
  }

  /**
   * getDatabaseUser returns a string (empty by default or set from env).
   */
  public function testGetDatabaseUserReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getDatabaseUser());
  }

  /**
   * getDatabasePassword returns a string (empty by default or set from env).
   */
  public function testGetDatabasePasswordReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getDatabasePassword());
  }

  /**
   * getDatabaseDB returns a string (empty by default or set from env).
   */
  public function testGetDatabaseDBReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getDatabaseDB());
  }

  /**
   * getDatabaseServer returns a string (empty by default or set from env).
   */
  public function testGetDatabaseServerReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getDatabaseServer());
  }

  /**
   * getDatabasePort returns a string (defaults to "0" or set from env).
   */
  public function testGetDatabasePortReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getDatabasePort());
  }

  /**
   * getPepper returns empty string for a negative index.
   */
  public function testGetPepperNegativeIndexReturnsEmpty(): void {
    $this->assertSame("", StartupConfig::getInstance()->getPepper(-1));
  }

  /**
   * getPepper returns empty string for an index equal to the array length (out of bounds).
   */
  public function testGetPepperOutOfBoundsReturnsEmpty(): void {
    $this->assertSame("", StartupConfig::getInstance()->getPepper(4));
  }

  /**
   * getPepper returns empty string for a very large out-of-bounds index.
   */
  public function testGetPepperVeryLargeIndexReturnsEmpty(): void {
    $this->assertSame("", StartupConfig::getInstance()->getPepper(999));
  }

  /**
   * getPepper returns a string (possibly empty) for each valid index 0-3.
   */
  public function testGetPepperValidIndexReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getPepper(0));
    $this->assertIsString(StartupConfig::getInstance()->getPepper(1));
    $this->assertIsString(StartupConfig::getInstance()->getPepper(2));
    $this->assertIsString(StartupConfig::getInstance()->getPepper(3));
  }

  /**
   * getVersion returns a non-empty string starting with "v".
   */
  public function testGetVersionReturnsString(): void {
    $v = StartupConfig::getInstance()->getVersion();
    $this->assertIsString($v);
    $this->assertNotEmpty($v);
    $this->assertStringStartsWith('v', $v);
  }

  /**
   * getBuild returns a non-empty string.
   */
  public function testGetBuildReturnsString(): void {
    $this->assertIsString(StartupConfig::getInstance()->getBuild());
  }

  /**
   * getHost returns the value of $_SERVER['SERVER_NAME'] when it is set.
   */
  public function testGetHostReturnsStringWhenServerNameSet(): void {
    $original = $_SERVER['SERVER_NAME'] ?? null;
    $_SERVER['SERVER_NAME'] = 'test.example.com';
    $this->assertSame('test.example.com', StartupConfig::getInstance()->getHost());
    if ($original !== null) {
      $_SERVER['SERVER_NAME'] = $original;
    }
    else {
      unset($_SERVER['SERVER_NAME']);
    }
  }

  /**
   * getHost returns an empty string when $_SERVER['SERVER_NAME'] is not set.
   */
  public function testGetHostReturnsEmptyStringWhenServerNameNotSet(): void {
    $original = $_SERVER['SERVER_NAME'] ?? null;
    unset($_SERVER['SERVER_NAME']);
    $this->assertSame("", StartupConfig::getInstance()->getHost());
    if ($original !== null) {
      $_SERVER['SERVER_NAME'] = $original;
    }
  }

  /**
   * getHost returns an empty string when $_SERVER['SERVER_NAME'] is explicitly null.
   */
  public function testGetHostReturnsEmptyStringWhenServerNameIsNull(): void {
    $original = $_SERVER['SERVER_NAME'] ?? null;
    $_SERVER['SERVER_NAME'] = null;
    $this->assertSame("", StartupConfig::getInstance()->getHost());
    if ($original !== null) {
      $_SERVER['SERVER_NAME'] = $original;
    }
  }
}
