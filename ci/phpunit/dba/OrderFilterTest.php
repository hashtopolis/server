<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class OrderFilterTest extends TestBase {
  /** Verify getBy returns the column key. */
  public function testGetBy(): void {
    $order = new OrderFilter(HashType::IS_SALTED, 'ASC');
    $this->assertEquals(HashType::IS_SALTED, $order->getBy());
  }
  
  /** Verify getType returns the sort direction. */
  public function testGetType(): void {
    $order = new OrderFilter(HashType::IS_SALTED, 'DESC');
    $this->assertEquals('DESC', $order->getType());
  }
  
  /** Verify basic query string without table prefix. */
  public function testQueryStringBasic(): void {
    $order = new OrderFilter(HashType::IS_SALTED, 'ASC');
    $this->assertEquals(
      'isSalted ASC',
      $order->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify table prefix is included when includeTable=true. */
  public function testQueryStringWithTable(): void {
    $order = new OrderFilter(HashType::IS_SALTED, 'ASC');
    $this->assertEquals(
      'Hashlist.isSalted ASC',
      $order->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify mapped table name (htp_User) appears in the query string. */
  public function testQueryStringMappedTable(): void {
    $order = new OrderFilter(User::USERNAME, 'ASC');
    $this->assertEquals(
      'htp_User.username ASC',
      $order->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /** Verify DESC sort direction works. */
  public function testQueryStringDesc(): void {
    $order = new OrderFilter(HashType::HASH_TYPE_ID, 'DESC');
    $this->assertEquals(
      'Hashlist.hashTypeId DESC',
      $order->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify overrideFactory is used regardless of passed factory. */
  public function testQueryStringOverrideFactory(): void {
    $order = new OrderFilter(HashType::IS_SALTED, 'ASC', Factory::getHashlistFactory());
    $this->assertEquals(
      'Hashlist.isSalted ASC',
      $order->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /** Verify mapped column (htp_end) resolves correctly. */
  public function testQueryStringMappedColumn(): void {
    $order = new OrderFilter(HealthCheckAgent::END, 'ASC');
    $this->assertEquals(
      'HealthCheckAgent.htp_end ASC',
      $order->getQueryString(Factory::getHealthCheckAgentFactory(), true)
    );
  }
  
  /**
   * Create 3 hash types with isSalted 1, 5, 10 and order ASC.
   * Expect results in order 1, 5, 10.
   *
   * @throws Exception
   */
  public function testOrderAsc(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'c_' . $testId, 10, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'a_' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'b_' . $testId, 5, 0));
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $order = new OrderFilter(HashType::IS_SALTED, 'ASC');
    $results = Factory::getHashTypeFactory()->filter([
      Factory::FILTER => $scope,
      Factory::ORDER => $order,
    ]);
    
    $this->assertCount(3, $results);
    $this->assertEquals(1, $results[0]->getIsSalted());
    $this->assertEquals(5, $results[1]->getIsSalted());
    $this->assertEquals(10, $results[2]->getIsSalted());
  }
  
  /**
   * Create 3 hash types with isSalted 1, 5, 10 and order DESC.
   * Expect results in order 10, 5, 1.
   *
   * @throws Exception
   */
  public function testOrderDesc(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'c_' . $testId, 10, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'a_' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'b_' . $testId, 5, 0));
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $order = new OrderFilter(HashType::IS_SALTED, 'DESC');
    $results = Factory::getHashTypeFactory()->filter([
      Factory::FILTER => $scope,
      Factory::ORDER => $order,
    ]);
    
    $this->assertCount(3, $results);
    $this->assertEquals(10, $results[0]->getIsSalted());
    $this->assertEquals(5, $results[1]->getIsSalted());
    $this->assertEquals(1, $results[2]->getIsSalted());
  }
}
