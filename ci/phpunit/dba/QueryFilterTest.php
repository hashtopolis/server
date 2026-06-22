<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class QueryFilterTest extends TestBase {
  /** Verify basic '=' produces 'col=?' without table prefix. */
  public function testGetQueryStringBasic(): void {
    $filter = new QueryFilter(HashType::IS_SALTED, 5, '=');
    $this->assertEquals('isSalted=?', $filter->getQueryString(Factory::getHashTypeFactory()));
  }

  /** Verify table prefix is prepended when includeTable is true. */
  public function testGetQueryStringWithTable(): void {
    $filter = new QueryFilter(HashType::IS_SALTED, 5, '=');
    $this->assertEquals(
      'HashType.isSalted=?',
      $filter->getQueryString(Factory::getHashTypeFactory(), true)
    );
  }

  /** Verify null value with '=' produces 'col IS NULL'. */
  public function testGetQueryStringNullIsNull(): void {
    $filter = new QueryFilter(HashType::IS_SALTED, null, '=');
    $this->assertEquals('isSalted IS NULL ', $filter->getQueryString(Factory::getHashTypeFactory()));
  }

  /** Verify null value with '<>' produces 'col IS NOT NULL'. */
  public function testGetQueryStringNullIsNotNull(): void {
    $filter = new QueryFilter(HashType::IS_SALTED, null, '<>');
    $this->assertEquals('isSalted IS NOT NULL ', $filter->getQueryString(Factory::getHashTypeFactory()));
  }

  /** Verify '>' operator produces 'col>?'. */
  public function testGetQueryStringGreaterThan(): void {
    $filter = new QueryFilter(HashType::IS_SALTED, 5, '>');
    $this->assertEquals('isSalted>?', $filter->getQueryString(Factory::getHashTypeFactory()));
  }

  /** Verify '<>' operator with non-null value produces 'col<>?'. */
  public function testGetQueryStringNotEqual(): void {
    $filter = new QueryFilter(HashType::IS_SALTED, 5, '<>');
    $this->assertEquals('isSalted<>?', $filter->getQueryString(Factory::getHashTypeFactory()));
  }

  /** Verify overrideFactory resolves columns from override regardless of the passed factory. */
  public function testGetQueryStringOverrideFactory(): void {
    $filter = new QueryFilter(AccessGroup::GROUP_NAME, 'test', '=', Factory::getAccessGroupFactory());
    $this->assertEquals(
      'groupName=?',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }

  /** Verify overrideFactory with table prefix uses the override's table name. */
  public function testGetQueryStringOverrideFactoryWithTable(): void {
    $filter = new QueryFilter(AccessGroup::GROUP_NAME, 'test', '=', Factory::getAccessGroupFactory());
    $this->assertEquals(
      'AccessGroup.groupName=?',
      $filter->getQueryString(Factory::getHashTypeFactory(), true)
    );
  }

  /** Verify getValue returns the constructor value for non-null input. */
  public function testGetValue(): void {
    $filter = new QueryFilter(HashType::IS_SALTED, 42, '=');
    $this->assertSame(42, $filter->getValue());
  }

  /** Verify getValue returns null for null input. */
  public function testGetValueNull(): void {
    $filter = new QueryFilter(HashType::IS_SALTED, null, '=');
    $this->assertNull($filter->getValue());
  }

  /** Verify getHasValue returns true for non-null value. */
  public function testGetHasValueTrue(): void {
    $filter = new QueryFilter(HashType::IS_SALTED, 5, '=');
    $this->assertTrue($filter->getHasValue());
  }

  /** Verify getHasValue returns false for null value. */
  public function testGetHasValueFalse(): void {
    $filter = new QueryFilter(HashType::IS_SALTED, null, '=');
    $this->assertFalse($filter->getHasValue());
  }

  /** Verify mapped column resolves to 'htp_end' without table prefix. */
  public function testGetQueryStringMappedColumn(): void {
    $filter = new QueryFilter(HealthCheckAgent::END, 1, '=');
    $this->assertEquals(
      'htp_end=?',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory())
    );
  }

  /** Verify mapped column with table prefix resolves to 'HealthCheckAgent.htp_end'. */
  public function testGetQueryStringMappedColumnWithTable(): void {
    $filter = new QueryFilter(HealthCheckAgent::END, 1, '=');
    $this->assertEquals(
      'HealthCheckAgent.htp_end=?',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory(), true)
    );
  }

  /**
   * Create 3 hash types, filter with '=' — expect 1 matching row.
   *
   * @throws Exception
   */
  public function testFilterEquals(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 0, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $qF = new QueryFilter(HashType::IS_SLOW_HASH, 0, '=');
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $qF]]);

    $this->assertCount(3, $results);
  }

  /**
   * Create 3 hash types, filter with '<>' (is_slow_hash != 0) — expect 2 rows.
   *
   * @throws Exception
   */
  public function testFilterNotEqual(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 0, 1));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 0, 5));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 0, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $qF = new QueryFilter(HashType::IS_SLOW_HASH, 0, '<>');
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $qF]]);

    $this->assertCount(2, $results);
  }

  /**
   * Create 3 hash types, filter with '>' (isSalted > 3) — expect 2 rows.
   *
   * @throws Exception
   */
  public function testFilterGreaterThan(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 10, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 1, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $qF = new QueryFilter(HashType::IS_SALTED, 3, '>');
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $qF]]);

    $this->assertCount(2, $results);
  }

  /**
   * Create 2 hash types, filter with '=' that matches none — expect 0 rows.
   *
   * @throws Exception
   */
  public function testFilterNoMatch(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 10, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $qF = new QueryFilter(HashType::IS_SALTED, 444, '=');
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $qF]]);

    $this->assertCount(0, $results);
  }

  /**
   * Use columnFilter with QueryFilter (isSalted > 3).
   * Only IDs of the 2 matching rows should be returned.
   *
   * @throws Exception
   */
  public function testFilterWithColumnFilter(): void {
    $testId = uniqid();
    $ht1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 10, 0));
    $ht2 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 1, 0));
    $ht3 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 7, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $qF = new QueryFilter(HashType::IS_SALTED, 3, '>');
    $ids = Factory::getHashTypeFactory()->columnFilter(
      [Factory::FILTER => [$lF, $qF]], HashType::HASH_TYPE_ID
    );

    $this->assertCount(2, $ids);
    $this->assertEqualsCanonicalizing([$ht1->getId(), $ht3->getId()], $ids);
  }
}
