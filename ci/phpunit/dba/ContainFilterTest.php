<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class ContainFilterTest extends TestBase {
  /** Verify single-element array produces 'col IN (?)'. */
  public function testQueryStringSingleValue(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1]);
    $this->assertEquals(
      'hashlistId IN (?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify multi-element array produces 'col IN (?,?,?)'. */
  public function testQueryStringMultipleValues(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1, 2, 3]);
    $this->assertEquals(
      'hashlistId IN (?,?,?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify table prefix is included when includeTable=true. */
  public function testQueryStringWithTable(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1]);
    $this->assertEquals(
      'Hashlist.hashlistId IN (?)',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify NOT IN (?,?) with the notIn flag set to true. */
  public function testQueryStringNotIn(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1, 2], null, true);
    $this->assertEquals(
      'hashlistId NOT IN (?,?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify NOT IN with table prefix produces 'Table.col NOT IN (?,?)'. */
  public function testQueryStringNotInWithTable(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1, 2], null, true);
    $this->assertEquals(
      'Hashlist.hashlistId NOT IN (?,?)',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify empty value array produces 'FALSE' (match nothing). */
  public function testQueryStringEmptyValues(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, []);
    $this->assertEquals(
      'FALSE',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify empty value array with notIn=true produces 'TRUE' (match everything). */
  public function testQueryStringEmptyValuesInverse(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [], null, true);
    $this->assertEquals(
      'TRUE',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify mapped table name (htp_User) is used with IN. */
  public function testQueryStringMappedTable(): void {
    $filter = new ContainFilter(User::USER_ID, [1]);
    $this->assertEquals(
      'htp_User.userId IN (?)',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /** Verify overrideFactory forces column resolution from the override regardless of the passed factory. */
  public function testQueryStringOverrideFactory(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1], Factory::getHashlistFactory());
    $this->assertEquals(
      'hashlistId IN (?)',
      $filter->getQueryString(Factory::getUserFactory())
    );
  }
  
  /** Verify mapped column name (htp_end) is used when the column has dba_mapping=True. */
  public function testQueryStringMappedColumn(): void {
    $filter = new ContainFilter(HealthCheckAgent::END, [1, 2]);
    $this->assertEquals(
      'htp_end IN (?,?)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory())
    );
  }
  
  /** Verify mapped column name (htp_end) with table prefix produces 'Table.htp_end IN (?)'. */
  public function testQueryStringMappedColumnWithTable(): void {
    $filter = new ContainFilter(HealthCheckAgent::END, [1]);
    $this->assertEquals(
      'HealthCheckAgent.htp_end IN (?)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory(), true)
    );
  }
  
  /** Verify NOT IN (?) with mapped column and notIn=true. */
  public function testQueryStringMappedColumnNotIn(): void {
    $filter = new ContainFilter(HealthCheckAgent::END, [1], null, true);
    $this->assertEquals(
      'htp_end NOT IN (?)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory())
    );
  }
  
  /** Verify getValue returns the array passed to the constructor. */
  public function testGetValue(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1, 2, 3]);
    $this->assertEquals([1, 2, 3], $filter->getValue());
  }
  
  /** Verify getHasValue returns true for a non-empty array. */
  public function testGetHasValue(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1]);
    $this->assertTrue($filter->getHasValue());
  }
  
  /**
   * Create 4 hash types with isSalted 1, 5, 10, 20 and filter IN (1, 10).
   * Only the 2 matching rows should be returned.
   *
   * @throws Exception
   */
  public function testFilterIn(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testId, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3' . $testId, 10, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht4' . $testId, 20, 0));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $cF = new ContainFilter(HashType::IS_SALTED, [1, 10]);
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);
    
    $this->assertCount(2, $result);
    foreach ($result as $ht) {
      $this->assertContains($ht->getIsSalted(), [1, 10]);
    }
  }
  
  /**
   * Create 4 hash types with isSalted 1, 5, 10, 20 and filter NOT IN (1, 10).
   * Only the 2 non-matching rows (5, 20) should be returned.
   *
   * @throws Exception
   */
  public function testFilterNotIn(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testId, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3' . $testId, 10, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht4' . $testId, 20, 0));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $cF = new ContainFilter(HashType::IS_SALTED, [1, 10], null, true);
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);
    
    $this->assertCount(2, $result);
    foreach ($result as $ht) {
      $this->assertContains($ht->getIsSalted(), [5, 20]);
    }
  }
  
  /**
   * Create a hash type and filter with IN ([]) — empty values produce
   * 'FALSE', so the result should be empty.
   *
   * @throws Exception
   */
  public function testFilterEmptyValues(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testId, 1, 0));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $cF = new ContainFilter(HashType::IS_SALTED, []);
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);
    
    $this->assertCount(0, $result);
  }
  
  /**
   * Create 2 hash types and filter with NOT IN ([]) — empty inverse
   * produces 'TRUE', so all scoped rows should be returned.
   *
   * @throws Exception
   */
  public function testFilterEmptyValuesInverse(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testId, 5, 0));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $cF = new ContainFilter(HashType::IS_SALTED, [], null, true);
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);
    
    $this->assertCount(2, $result);
  }
  
  /**
   * Use columnFilter with IN (1, 10) — only the 2 matching hash type IDs
   * should be returned.
   *
   * @throws Exception
   */
  public function testFilterInWithColumnFilter(): void {
    $testId = uniqid();
    $ht1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testId, 5, 0));
    $ht3 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3' . $testId, 10, 0));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $cF = new ContainFilter(HashType::IS_SALTED, [1, 10]);
    $ids = Factory::getHashTypeFactory()->columnFilter([Factory::FILTER => [$lF, $cF]], HashType::HASH_TYPE_ID);
    
    $this->assertCount(2, $ids);
    $this->assertEqualsCanonicalizing([$ht1->getId(), $ht3->getId()], $ids);
  }
}
