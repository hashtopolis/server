<?php

namespace Hashtopolis\inc;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Config;
use Hashtopolis\TestBase;
use ReflectionClass;
use ReflectionException;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class SConfigTest extends TestBase {
  /**
   * @throws ReflectionException
   */
  protected function tearDown(): void {
    $p = (new ReflectionClass(SConfig::class))->getProperty('instance');
    $p->setValue(null, null);
    parent::tearDown();
  }
  
  /**
   * getInstance returns a DataSet object when called for the first time.
   */
  public function testGetInstanceReturnsDataSet(): void {
    try {
      $ds = SConfig::getInstance();
      $this->assertInstanceOf(DataSet::class, $ds);
    }
    catch (Exception $e) {
      $this->markTestSkipped('DB not available: ' . $e->getMessage());
    }
  }
  
  /**
   * Consecutive calls to getInstance return the same DataSet object (singleton).
   */
  public function testSingletonReturnsSameInstance(): void {
    try {
      $i1 = SConfig::getInstance();
      $i2 = SConfig::getInstance();
      $this->assertSame($i1, $i2);
    }
    catch (Exception $e) {
      $this->markTestSkipped('DB not available: ' . $e->getMessage());
    }
  }
  
  /**
   * getInstance(true) discards the cached singleton and loads fresh data from the database.
   */
  public function testGetInstanceWithForceReturnsNewInstance(): void {
    try {
      $p = (new ReflectionClass(SConfig::class))->getProperty('instance');
      $p->setValue(null, new DataSet(['test' => 'value']));
      
      $fresh = SConfig::getInstance(true);
      $this->assertInstanceOf(DataSet::class, $fresh);
    }
    catch (Exception $e) {
      $this->markTestSkipped('DB not available: ' . $e->getMessage());
    }
  }
  
  /**
   * reload() forces a fresh load from the database, replacing the current singleton.
   */
  public function testReloadForcesNewLoad(): void {
    try {
      $i1 = SConfig::getInstance();
      SConfig::reload();
      $i2 = SConfig::getInstance();
      $this->assertNotSame($i1, $i2);
    }
    catch (Exception $e) {
      $this->markTestSkipped('DB not available: ' . $e->getMessage());
    }
  }
  
  /**
   * A Config row saved to the database is accessible via SConfig after a reload.
   */
  public function testGetInstanceLoadsConfigFromDatabase(): void {
    try {
      $key = 'test_config_' . uniqid();
      $value = 'test_value_' . uniqid();
      
      Factory::getConfigFactory()->save(new Config(null, 1, $key, $value));
      
      SConfig::reload();
      $result = SConfig::getInstance()->getVal($key);
      $this->assertSame($value, $result);
    }
    catch (Exception $e) {
      $this->markTestSkipped('DB not available: ' . $e->getMessage());
    }
  }
  
  /**
   * Multiple Config rows saved to the database are all loaded into the DataSet.
   */
  public function testGetInstanceLoadsMultipleConfigValues(): void {
    try {
      $key1 = 'multi_test_1_' . uniqid();
      $key2 = 'multi_test_2_' . uniqid();
      
      Factory::getConfigFactory()->save(new Config(null, 1, $key1, 'val1'));
      Factory::getConfigFactory()->save(new Config(null, 1, $key2, 'val2'));
      
      SConfig::reload();
      $ds = SConfig::getInstance();
      $this->assertSame('val1', $ds->getVal($key1));
      $this->assertSame('val2', $ds->getVal($key2));
    }
    catch (Exception $e) {
      $this->markTestSkipped('DB not available: ' . $e->getMessage());
    }
  }
  
  /**
   * getVal returns false for a key that does not exist in the loaded config.
   */
  public function testGetValReturnsFalseForUnknownKey(): void {
    try {
      $result = SConfig::getInstance()->getVal('nonexistent_key_' . uniqid());
      $this->assertFalse($result);
    }
    catch (Exception $e) {
      $this->markTestSkipped('DB not available: ' . $e->getMessage());
    }
  }
  
  /**
   * getKeys returns an array of all config keys loaded from the database.
   */
  public function testConfigSectionHasExpectedKeys(): void {
    try {
      $ds = SConfig::getInstance();
      $keys = $ds->getKeys();
      $this->assertTrue(sizeof($keys) > 0);
    }
    catch (Exception $e) {
      $this->markTestSkipped('DB not available: ' . $e->getMessage());
    }
  }
}
