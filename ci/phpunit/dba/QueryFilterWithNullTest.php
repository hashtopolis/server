<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class QueryFilterWithNullTest extends TestBase {
  /** Verify basic '=' with matchNull=true produces '(col=? OR col IS NULL)'. */
  public function testGetQueryStringMatchNullTrue(): void {
    $filter = new QueryFilterWithNull(HashType::IS_SALTED, 5, '=', true);
    $this->assertEquals(
      '(isSalted=? OR isSalted IS NULL)',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }

  /** Verify basic '=' with matchNull=false produces '(col=? OR col IS NOT NULL)'. */
  public function testGetQueryStringMatchNullFalse(): void {
    $filter = new QueryFilterWithNull(HashType::IS_SALTED, 5, '=', false);
    $this->assertEquals(
      '(isSalted=? OR isSalted IS NOT NULL)',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }

  /** Verify '>' with matchNull=true produces '(col>? OR col IS NULL)'. */
  public function testGetQueryStringMatchNullTrueGreaterThan(): void {
    $filter = new QueryFilterWithNull(HashType::IS_SALTED, 5, '>', true);
    $this->assertEquals(
      '(isSalted>? OR isSalted IS NULL)',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }

  /** Verify table prefix wraps both column references. */
  public function testGetQueryStringWithTable(): void {
    $filter = new QueryFilterWithNull(HashType::IS_SALTED, 5, '=', true);
    $this->assertEquals(
      '(HashType.isSalted=? OR HashType.isSalted IS NULL)',
      $filter->getQueryString(Factory::getHashTypeFactory(), true)
    );
  }

  /** Verify null value with '=' produces IS NULL regardless of matchNull. */
  public function testGetQueryStringNullValueIsNull(): void {
    $filter = new QueryFilterWithNull(HashType::IS_SALTED, null, '=', true);
    $this->assertEquals(
      'isSalted IS NULL ',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }

  /** Verify null value with '<>' produces IS NOT NULL regardless of matchNull. */
  public function testGetQueryStringNullValueIsNotNull(): void {
    $filter = new QueryFilterWithNull(HashType::IS_SALTED, null, '<>', false);
    $this->assertEquals(
      'isSalted IS NOT NULL ',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }

  /** Verify overrideFactory changes column resolution. */
  public function testGetQueryStringOverrideFactory(): void {
    $filter = new QueryFilterWithNull(
      AccessGroup::GROUP_NAME, 'test', '=', true, Factory::getAccessGroupFactory()
    );
    $this->assertEquals(
      '(groupName=? OR groupName IS NULL)',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }

  /** Verify overrideFactory with table prefix uses the override's table name. */
  public function testGetQueryStringOverrideFactoryWithTable(): void {
    $filter = new QueryFilterWithNull(
      AccessGroup::GROUP_NAME, 'test', '=', true, Factory::getAccessGroupFactory()
    );
    $this->assertEquals(
      '(AccessGroup.groupName=? OR AccessGroup.groupName IS NULL)',
      $filter->getQueryString(Factory::getHashTypeFactory(), true)
    );
  }

  /** Verify getValue returns the constructor value for non-null input. */
  public function testGetValue(): void {
    $filter = new QueryFilterWithNull(HashType::IS_SALTED, 42, '=', true);
    $this->assertSame(42, $filter->getValue());
  }

  /** Verify getValue returns null for null input. */
  public function testGetValueNull(): void {
    $filter = new QueryFilterWithNull(HashType::IS_SALTED, null, '=', true);
    $this->assertNull($filter->getValue());
  }

  /** Verify getHasValue returns true for non-null value. */
  public function testGetHasValueTrue(): void {
    $filter = new QueryFilterWithNull(HashType::IS_SALTED, 5, '=', true);
    $this->assertTrue($filter->getHasValue());
  }

  /** Verify getHasValue returns false for null value. */
  public function testGetHasValueFalse(): void {
    $filter = new QueryFilterWithNull(HashType::IS_SALTED, null, '=', true);
    $this->assertFalse($filter->getHasValue());
  }

  /** Verify mapped column with matchNull=true resolves to '(htp_end=? OR htp_end IS NULL)'. */
  public function testGetQueryStringMappedColumnMatchNullTrue(): void {
    $filter = new QueryFilterWithNull(HealthCheckAgent::END, 1, '=', true);
    $this->assertEquals(
      '(htp_end=? OR htp_end IS NULL)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory())
    );
  }

  /** Verify mapped column with matchNull=false resolves to '(htp_end=? OR htp_end IS NOT NULL)'. */
  public function testGetQueryStringMappedColumnMatchNullFalse(): void {
    $filter = new QueryFilterWithNull(HealthCheckAgent::END, 1, '=', false);
    $this->assertEquals(
      '(htp_end=? OR htp_end IS NOT NULL)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory())
    );
  }

  /** Verify mapped column with table prefix uses 'HealthCheckAgent.htp_end'. */
  public function testGetQueryStringMappedColumnWithTable(): void {
    $filter = new QueryFilterWithNull(HealthCheckAgent::END, 1, '=', true);
    $this->assertEquals(
      '(HealthCheckAgent.htp_end=? OR HealthCheckAgent.htp_end IS NULL)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory(), true)
    );
  }

  /**
   * Create 3 hash types, filter with matchNull=false and '='
   * (col=? OR col IS NOT NULL). Since all rows have non-null isSalted,
   * the expected-matching rows (isSalted=5) should be returned.
   *
   * @throws Exception
   */
  public function testFilterMatchNullFalse(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 0, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $qF = new QueryFilterWithNull(HashType::IS_SLOW_HASH, 0, '=', false);
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $qF]]);

    $this->assertCount(3, $results);
  }

  /**
   * Create 3 hash types, filter with matchNull=true and '>'
   * (col>? OR col IS NULL). Since all rows have non-null isSalted,
   * only the matching rows (isSalted > 3) should be returned.
   *
   * @throws Exception
   */
  public function testFilterMatchNullTrueGreaterThan(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 10, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 7, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $qF = new QueryFilterWithNull(HashType::IS_SALTED, 3, '>', true);
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $qF]]);

    $this->assertCount(2, $results);
  }
}
