<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\HealthCheck;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class MassUpdateSetTest extends TestBase {
  /** Verify getMatchValue returns the match string. */
  public function testGetMatchValue(): void {
    $set = new MassUpdateSet('key1', 'val1');
    $this->assertEquals('key1', $set->getMatchValue());
  }
  
  /** Verify getUpdateValue returns the update string. */
  public function testGetUpdateValueString(): void {
    $set = new MassUpdateSet('key1', 'val1');
    $this->assertEquals('val1', $set->getUpdateValue());
  }
  
  /** Verify getUpdateValue returns the update int. */
  public function testGetUpdateValueInt(): void {
    $set = new MassUpdateSet('key1', 42);
    $this->assertSame(42, $set->getUpdateValue());
  }
  
  /** Verify getUpdateValue returns null. */
  public function testGetUpdateValueNull(): void {
    $set = new MassUpdateSet('key1', null);
    $this->assertNull($set->getUpdateValue());
  }
  
  /** Verify getMassQuery returns the correct SQL fragment. */
  public function testGetMassQuery(): void {
    $set = new MassUpdateSet('key1', 'val1');
    $this->assertEquals(
      'WHEN columnName = ? THEN ? ',
      $set->getMassQuery('columnName')
    );
  }
  
  /**
   * Create 3 hash types, mass-update 2 with string values.
   * Verify updates took effect and the untouched row is unchanged.
   *
   * @throws Exception
   */
  public function testMassSingleUpdateString(): void {
    $testId = uniqid();
    $ht1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 0, 0));
    $ht2 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 0, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 0, 0));
    
    $this->assertTrue($ht1 instanceof HashType);
    $this->assertTrue($ht2 instanceof HashType);
    
    $updates = [
      new MassUpdateSet($ht1->getDescription(), 99),
      new MassUpdateSet($ht2->getDescription(), 88),
    ];
    
    $result = Factory::getHashTypeFactory()->massSingleUpdate(
      HashType::DESCRIPTION, HashType::IS_SALTED, $updates
    );
    
    $this->assertTrue($result);
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => $scope]);
    
    $this->assertCount(3, $results);
    foreach ($results as $ht) {
      if ($ht->getDescription() === $ht1->getDescription()) {
        $this->assertEquals(99, $ht->getIsSalted());
      }
      elseif ($ht->getDescription() === $ht2->getDescription()) {
        $this->assertEquals(88, $ht->getIsSalted());
      }
      else {
        $this->assertEquals(0, $ht->getIsSalted());
      }
    }
  }
  
  /**
   * Create 3 hash types, mass-update 1 with an integer value.
   * The ELSE 2147483648 clause is triggered for integer updates.
   * Only the matched row should change; the other 2 keep their value.
   *
   * @throws Exception
   */
  public function testMassSingleUpdateIntValue(): void {
    $testId = uniqid();
    $ht1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 0, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 0, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 0, 0));
    
    $this->assertTrue($ht1 instanceof HashType);
    
    $updates = [
      new MassUpdateSet($ht1->getDescription(), 100),
    ];
    
    $result = Factory::getHashTypeFactory()->massSingleUpdate(
      HashType::DESCRIPTION, HashType::IS_SALTED, $updates
    );
    
    $this->assertTrue($result);
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => $scope]);
    
    $this->assertCount(3, $results);
    foreach ($results as $ht) {
      if ($ht->getDescription() === $ht1->getDescription()) {
        $this->assertEquals(100, $ht->getIsSalted());
      }
      else {
        $this->assertEquals(0, $ht->getIsSalted());
      }
    }
  }
  
  /**
   * Empty updates array should return null.
   *
   * @throws Exception
   */
  public function testMassSingleUpdateEmptyReturnsNull(): void {
    $result = Factory::getHashTypeFactory()->massSingleUpdate(
      HashType::DESCRIPTION, HashType::IS_SALTED, []
    );
    $this->assertNull($result);
  }
  
  /**
   * massSingleUpdate with a mapped update column (HealthCheckAgent::END → htp_end).
   * Create 3 HealthCheckAgent rows, update 2, verify the changes.
   *
   * @throws Exception
   */
  public function testMassSingleUpdateWithMappedColumn(): void {
    $testId = uniqid();
    $prefix = 'hca_' . $testId;
    $agent = $this->createDatabaseObject(Factory::getAgentFactory(), new Agent(null, '', '', 0, '', '', 0, 0, 0, '', '', 0, '', null, 0, ''));
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, $prefix . '_ht', 0, 0));
    $cbt = $this->createDatabaseObject(Factory::getCrackerBinaryTypeFactory(), new CrackerBinaryType(null, '', 0));
    $cb = $this->createDatabaseObject(Factory::getCrackerBinaryFactory(), new CrackerBinary(null, $cbt->getId(), '', '', ''));
    $healthCheck = $this->createDatabaseObject(Factory::getHealthCheckFactory(), new HealthCheck(null, 0, 0, 0, $hashType->getId(), $cb->getId(), 0, ''));
    
    $hca1 = $this->createDatabaseObject(Factory::getHealthCheckAgentFactory(), new HealthCheckAgent(null, $healthCheck->getId(), $agent->getId(), 0, 0, 0, 0, 100, ''));
    $hca2 = $this->createDatabaseObject(Factory::getHealthCheckAgentFactory(), new HealthCheckAgent(null, $healthCheck->getId(), $agent->getId(), 0, 0, 0, 0, 200, ''));
    $this->createDatabaseObject(Factory::getHealthCheckAgentFactory(), new HealthCheckAgent(null, $healthCheck->getId(), $agent->getId(), 0, 0, 0, 0, 300, ''));
    
    $this->assertTrue($hca1 instanceof HealthCheckAgent);
    $this->assertTrue($hca2 instanceof HealthCheckAgent);
    
    $updates = [
      new MassUpdateSet($hca1->getId(), 999),
      new MassUpdateSet($hca2->getId(), 888),
    ];
    
    $result = Factory::getHealthCheckAgentFactory()->massSingleUpdate(
      HealthCheckAgent::HEALTH_CHECK_AGENT_ID, HealthCheckAgent::END, $updates
    );
    
    $this->assertTrue($result);
    
    $scope1 = new QueryFilter(HealthCheckAgent::HEALTH_CHECK_AGENT_ID, $hca1->getId(), '=');
    $updated1 = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => $scope1], true);
    $this->assertInstanceOf(HealthCheckAgent::class, $updated1);
    $this->assertEquals(999, $updated1->getEnd());
    
    $scope2 = new QueryFilter(HealthCheckAgent::HEALTH_CHECK_AGENT_ID, $hca2->getId(), '=');
    $updated2 = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => $scope2], true);
    $this->assertInstanceOf(HealthCheckAgent::class, $updated2);
    $this->assertEquals(888, $updated2->getEnd());
  }
}
