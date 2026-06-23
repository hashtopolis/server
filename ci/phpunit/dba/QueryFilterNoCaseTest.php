<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class QueryFilterNoCaseTest extends TestBase {
  /** Verify basic '=' produces '(LOWER(col) =? OR col=?)' without table. */
  public function testGetQueryStringBasic(): void {
    $filter = new QueryFilterNoCase(HashType::DESCRIPTION, 'test', '=');
    $this->assertEquals(
      '(LOWER(description) =? OR description=?)',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }
  
  /** Verify table prefix wraps both column references. */
  public function testGetQueryStringWithTable(): void {
    $filter = new QueryFilterNoCase(HashType::DESCRIPTION, 'test', '=');
    $this->assertEquals(
      '(LOWER(HashType.description) =? OR HashType.description=?)',
      $filter->getQueryString(Factory::getHashTypeFactory(), true)
    );
  }
  
  /** Verify null value with '=' produces 'col IS NULL'. */
  public function testGetQueryStringNullIsNull(): void {
    $filter = new QueryFilterNoCase(HashType::DESCRIPTION, null, '=');
    $this->assertEquals(
      'description IS NULL ',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }
  
  /** Verify null value with '<>' produces 'col IS NOT NULL'. */
  public function testGetQueryStringNullIsNotNull(): void {
    $filter = new QueryFilterNoCase(HashType::DESCRIPTION, null, '<>');
    $this->assertEquals(
      'description IS NOT NULL ',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }
  
  /** Verify '>' operator produces '(LOWER(col) >? OR col>?)'. */
  public function testGetQueryStringGreaterThan(): void {
    $filter = new QueryFilterNoCase(HashType::DESCRIPTION, 'test', '>');
    $this->assertEquals(
      '(LOWER(description) >? OR description>?)',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }
  
  /** Verify overrideFactory changes column resolution. */
  public function testGetQueryStringOverrideFactory(): void {
    $filter = new QueryFilterNoCase(AccessGroup::GROUP_NAME, 'test', '=', Factory::getAccessGroupFactory());
    $this->assertEquals(
      '(LOWER(groupName) =? OR groupName=?)',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }
  
  /** Verify overrideFactory with table prefix uses the override's table name. */
  public function testGetQueryStringOverrideFactoryWithTable(): void {
    $filter = new QueryFilterNoCase(AccessGroup::GROUP_NAME, 'test', '=', Factory::getAccessGroupFactory());
    $this->assertEquals(
      '(LOWER(AccessGroup.groupName) =? OR AccessGroup.groupName=?)',
      $filter->getQueryString(Factory::getHashTypeFactory(), true)
    );
  }
  
  /** Verify getValue returns [value, value] for non-null input. */
  public function testGetValue(): void {
    $filter = new QueryFilterNoCase(HashType::DESCRIPTION, 'hello', '=');
    $this->assertSame(['hello', 'hello'], $filter->getValue());
  }
  
  /** Verify getValue returns null for null input. */
  public function testGetValueNull(): void {
    $filter = new QueryFilterNoCase(HashType::DESCRIPTION, null, '=');
    $this->assertNull($filter->getValue());
  }
  
  /** Verify getHasValue returns true for non-null value. */
  public function testGetHasValueTrue(): void {
    $filter = new QueryFilterNoCase(HashType::DESCRIPTION, 'test', '=');
    $this->assertTrue($filter->getHasValue());
  }
  
  /** Verify getHasValue returns false for null value. */
  public function testGetHasValueFalse(): void {
    $filter = new QueryFilterNoCase(HashType::DESCRIPTION, null, '=');
    $this->assertFalse($filter->getHasValue());
  }

  /** Verify mapped column resolves to 'htp_end' in both LOWER and bare form. */
  public function testGetQueryStringMappedColumn(): void {
    $filter = new QueryFilterNoCase(HealthCheckAgent::END, 'test', '=');
    $this->assertEquals(
      '(LOWER(htp_end) =? OR htp_end=?)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory())
    );
  }

  /** Verify mapped column with table prefix wraps 'HealthCheckAgent.htp_end'. */
  public function testGetQueryStringMappedColumnWithTable(): void {
    $filter = new QueryFilterNoCase(HealthCheckAgent::END, 'test', '=');
    $this->assertEquals(
      '(LOWER(HealthCheckAgent.htp_end) =? OR HealthCheckAgent.htp_end=?)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory(), true)
    );
  }
  
  /**
   * Create 2 hash types with mixed-case descriptions, filter with lowercase.
   * Case-insensitive match should find both.
   *
   * @throws Exception
   */
  public function testFilterCaseInsensitive(): void {
    $testId = uniqid();
    $label1 = 'FOO_' . $testId;
    $label2 = 'foo_' . $testId;
    $label3 = 'bar_' . $testId;
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, $label1, 0, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, $label2, 0, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, $label3, 0, 0));
    
    $qF = new QueryFilterNoCase(HashType::DESCRIPTION, 'foo_' . $testId, '=');
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => $qF]);
    
    $this->assertCount(2, $results);
  }
  
  /**
   * Filter with a value that matches no rows — expect 0 results.
   *
   * @throws Exception
   */
  public function testFilterNoMatch(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'match_' . $testId, 0, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'other_' . $testId, 0, 0));
    
    $qF = new QueryFilterNoCase(HashType::DESCRIPTION, 'nonexistent_' . $testId, '=');
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => $qF]);
    
    $this->assertCount(0, $results);
  }
}
