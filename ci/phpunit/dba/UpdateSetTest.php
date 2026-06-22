<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class UpdateSetTest extends TestBase {
  /** Verify getQuery returns 'column=?' without table prefix. */
  public function testGetQueryBasic(): void {
    $set = new UpdateSet(HashType::IS_SALTED, 1);
    $this->assertEquals(
      'isSalted=?',
      $set->getQuery(Factory::getHashlistFactory())
    );
  }
  
  /** Verify getQuery includes table prefix when includeTable=true. */
  public function testGetQueryWithTable(): void {
    $set = new UpdateSet(HashType::IS_SALTED, 1);
    $this->assertEquals(
      'Hashlist.isSalted=?',
      $set->getQuery(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify mapped column name (htp_end) resolves correctly. */
  public function testGetQueryMappedColumn(): void {
    $set = new UpdateSet(HealthCheckAgent::END, 5);
    $this->assertEquals(
      'HealthCheckAgent.htp_end=?',
      $set->getQuery(Factory::getHealthCheckAgentFactory(), true)
    );
  }
  
  /** Verify getValue returns the constructor value (string). */
  public function testGetValueString(): void {
    $set = new UpdateSet(HashType::DESCRIPTION, 'new_desc');
    $this->assertEquals('new_desc', $set->getValue());
  }
  
  /** Verify getValue returns the constructor value (int). */
  public function testGetValueInt(): void {
    $set = new UpdateSet(HashType::IS_SALTED, 99);
    $this->assertSame(99, $set->getValue());
  }
  
  /** Verify getValue returns null. */
  public function testGetValueNull(): void {
    $set = new UpdateSet(HashType::IS_SALTED, null);
    $this->assertNull($set->getValue());
  }
  
  /**
   * Create 3 hash types with isSalted = 0, then mass-update isSalted to 99.
   * Verify all 3 rows are updated.
   *
   * @throws Exception
   */
  public function testMassUpdateSingleSet(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 0, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 0, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 0, 0));
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $update = new UpdateSet(HashType::IS_SALTED, 99);
    
    $result = Factory::getHashTypeFactory()->massUpdate([
      Factory::UPDATE => $update,
      Factory::FILTER => $scope,
    ]);
    
    $this->assertTrue($result);
    
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => $scope]);
    $this->assertCount(3, $results);
    foreach ($results as $ht) {
      $this->assertEquals(99, $ht->getIsSalted());
    }
  }
  
  /**
   * Create 3 hash types, then mass-update with 2 UpdateSets on different
   * columns. Verify both columns are updated.
   *
   * @throws Exception
   */
  public function testMassUpdateMultipleSets(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 0, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 0, 0));
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $updates = [
      new UpdateSet(HashType::IS_SALTED, 77),
      new UpdateSet(HashType::IS_SLOW_HASH, 88),
    ];
    
    $result = Factory::getHashTypeFactory()->massUpdate([
      Factory::UPDATE => $updates,
      Factory::FILTER => $scope,
    ]);
    
    $this->assertTrue($result);
    
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => $scope]);
    $this->assertCount(2, $results);
    foreach ($results as $ht) {
      $this->assertEquals(77, $ht->getIsSalted());
      $this->assertEquals(88, $ht->getIsSlowHash());
    }
  }
}
