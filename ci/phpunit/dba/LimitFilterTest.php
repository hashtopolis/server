<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\TestBase;
use InvalidArgumentException;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class LimitFilterTest extends TestBase {
  /** Verify only-limit produces the limit value alone. */
  public function testQueryStringOnlyLimit(): void {
    $filter = new LimitFilter(10);
    $this->assertEquals('10', $filter->getQueryString());
  }
  
  /** Verify limit with offset produces '<n> OFFSET <m>'. */
  public function testQueryStringWithOffset(): void {
    $filter = new LimitFilter(10, 5);
    $this->assertEquals('10 OFFSET 5', $filter->getQueryString());
  }
  
  /** Verify zero limit is accepted. */
  public function testQueryStringZeroLimit(): void {
    $filter = new LimitFilter(0);
    $this->assertEquals('0', $filter->getQueryString());
  }
  
  /** Verify zero offset is treated as null (loose comparison quirk). */
  public function testQueryStringZeroOffset(): void {
    $filter = new LimitFilter(5, 0);
    $this->assertEquals('5', $filter->getQueryString());
  }
  
  /** Verify numeric string limit is cast to int. */
  public function testQueryStringStringIntLimit(): void {
    $filter = new LimitFilter("5");
    $this->assertEquals('5', $filter->getQueryString());
  }
  
  /** Verify numeric string limit and offset both work. */
  public function testQueryStringWithBothStringInt(): void {
    $filter = new LimitFilter("5", "3");
    $this->assertEquals('5 OFFSET 3', $filter->getQueryString());
  }
  
  /** Verify null offset produces only limit. */
  public function testNullOffset(): void {
    $filter = new LimitFilter(10, null);
    $this->assertEquals('10', $filter->getQueryString());
  }
  
  /** Verify negative limit throws. */
  public function testInvalidLimitThrows(): void {
    $this->expectException(InvalidArgumentException::class);
    new LimitFilter(-1);
  }
  
  /** Verify non-numeric limit string throws. */
  public function testInvalidLimitStringThrows(): void {
    $this->expectException(InvalidArgumentException::class);
    new LimitFilter("abc");
  }
  
  /** Verify negative offset throws. */
  public function testInvalidOffsetThrows(): void {
    $this->expectException(InvalidArgumentException::class);
    new LimitFilter(5, -1);
  }
  
  /** Verify non-numeric offset string throws. */
  public function testInvalidOffsetStringThrows(): void {
    $this->expectException(InvalidArgumentException::class);
    new LimitFilter(5, "xyz");
  }
  
  /**
   * Create 5 hash types, limit to 2 — only 2 returned.
   *
   * @throws Exception
   */
  public function testLimitRestrictsResults(): void {
    $testId = uniqid();
    for ($i = 0; $i < 5; $i++) {
      $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht' . $i . '_' . $testId, 0, 0));
    }
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $limit = new LimitFilter(2);
    $results = Factory::getHashTypeFactory()->filter([
      Factory::FILTER => $scope,
      Factory::LIMIT => $limit,
    ]);
    
    $this->assertCount(2, $results);
  }
  
  /**
   * Create 3 hash types, limit to 10 — all 3 returned.
   *
   * @throws Exception
   */
  public function testLimitLargerThanResults(): void {
    $testId = uniqid();
    for ($i = 0; $i < 3; $i++) {
      $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht' . $i . '_' . $testId, 0, 0));
    }
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $limit = new LimitFilter(10);
    $results = Factory::getHashTypeFactory()->filter([
      Factory::FILTER => $scope,
      Factory::LIMIT => $limit,
    ]);
    
    $this->assertCount(3, $results);
  }
  
  /**
   * Create 3 hash types, limit to 0 — 0 results.
   *
   * @throws Exception
   */
  public function testLimitZeroResults(): void {
    $testId = uniqid();
    for ($i = 0; $i < 3; $i++) {
      $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht' . $i . '_' . $testId, 0, 0));
    }
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $limit = new LimitFilter(0);
    $results = Factory::getHashTypeFactory()->filter([
      Factory::FILTER => $scope,
      Factory::LIMIT => $limit,
    ]);
    
    $this->assertCount(0, $results);
  }
  
  /**
   * 3 hash types (isSalted 10, 5, 1). ORDER ASC, LIMIT 1 OFFSET 1 →
   * expect 1 result with isSalted = 5.
   *
   * @throws Exception
   */
  public function testLimitWithOffsetAndOrder(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 10, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 1, 0));
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $order = new OrderFilter(HashType::IS_SALTED, 'ASC');
    $limit = new LimitFilter(1, 1);
    $results = Factory::getHashTypeFactory()->filter([
      Factory::FILTER => $scope,
      Factory::ORDER => $order,
      Factory::LIMIT => $limit,
    ]);
    
    $this->assertCount(1, $results);
    $this->assertEquals(5, $results[0]->getIsSalted());
  }
}
