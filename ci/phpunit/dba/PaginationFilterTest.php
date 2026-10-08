<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\Hash;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class PaginationFilterTest extends TestBase {
  /** Verify basic pagination query string without table prefix. */
  public function testQueryStringBasic(): void {
    $filter = new PaginationFilter(HashType::IS_SALTED, 5, '>', HashType::HASH_TYPE_ID, 0);
    $this->assertEquals(
      '((isSalted>?) OR (isSalted=? AND hashTypeId>?))',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify table prefix is included when includeTable=true. */
  public function testQueryStringWithTable(): void {
    $filter = new PaginationFilter(HashType::IS_SALTED, 5, '>', HashType::HASH_TYPE_ID, 0);
    $this->assertEquals(
      '((Hashlist.isSalted>?) OR (Hashlist.isSalted=? AND Hashlist.hashTypeId>?))',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify a sort expression of a column is applied to the column and the cursor value. */
  public function testQueryStringSortExpression(): void {
    $filter = new PaginationFilter(Hash::HASH, 'abc', '>', Hash::HASH_ID, 0);
    $this->assertEquals(
      '((LEFT(Hash.hash, 1024)>LEFT(?, 1024)) OR (LEFT(Hash.hash, 1024)=LEFT(?, 1024) AND Hash.hashId>?))',
      $filter->getQueryString(Factory::getHashFactory(), true)
    );
  }
  
  /** Verify mixed cursor operators support DESC primary sort with ASC tie-breaker. */
  public function testQueryStringMixedOperators(): void {
    $filter = new PaginationFilter(HashType::IS_SALTED, 5, '<', HashType::HASH_TYPE_ID, 10, null, '>');
    $this->assertEquals(
      '((isSalted<?) OR (isSalted=? AND hashTypeId>?))',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }
  
  /** Verify mapped table name (htp_User) is used. */
  public function testQueryStringMappedTable(): void {
    $filter = new PaginationFilter(User::USER_ID, 1, '>', User::USERNAME, '');
    $this->assertEquals(
      '((htp_User.userId>?) OR (htp_User.userId=? AND htp_User.username>?))',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /** Verify overrideFactory resolves columns from the override. */
  public function testQueryStringOverrideFactory(): void {
    $filter = new PaginationFilter(HashType::IS_SALTED, 5, '>', HashType::HASH_TYPE_ID, 0, Factory::getHashlistFactory());
    $this->assertEquals(
      '((Hashlist.isSalted>?) OR (Hashlist.isSalted=? AND Hashlist.hashTypeId>?))',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /** Verify getValue returns [value, value, tieBreakerValue]. */
  public function testGetValue(): void {
    $filter = new PaginationFilter(HashType::IS_SALTED, 5, '>', HashType::HASH_TYPE_ID, 10);
    $this->assertEquals([5, 5, 10], $filter->getValue());
  }
  
  /** Verify getHasValue returns true when value is not null. */
  public function testGetHasValueTrue(): void {
    $filter = new PaginationFilter(HashType::IS_SALTED, 5, '>', HashType::HASH_TYPE_ID, 0);
    $this->assertTrue($filter->getHasValue());
  }
  
  /** Verify getHasValue returns false when value is null. */
  public function testGetHasValueFalse(): void {
    $filter = new PaginationFilter(HashType::IS_SALTED, null, '>', HashType::HASH_TYPE_ID, 0);
    $this->assertFalse($filter->getHasValue());
  }
  
  /**
   * Create 3 hash types with isSalted 1, 5, 10. Paginate with cursor 3.
   * Expect rows with isSalted > 3 (5 and 10).
   *
   * @throws Exception
   */
  public function testFilterPaginationGt(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 10, 0));
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $pf = new PaginationFilter(HashType::IS_SALTED, 3, '>', HashType::HASH_TYPE_ID, 0);
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$scope, $pf]]);
    
    $this->assertGreaterThanOrEqual(2, count($results));
    foreach ($results as $ht) {
      $this->assertGreaterThan(3, $ht->getIsSalted());
    }
  }
  
  /**
   * Create 3 hash types with isSalted 1, 5, 10 and use '<' operator
   * with cursor 6. Expect rows with isSalted < 6 (1 and 5).
   *
   * @throws Exception
   */
  public function testFilterPaginationLt(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1_' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2_' . $testId, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3_' . $testId, 10, 0));
    
    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $pf = new PaginationFilter(HashType::IS_SALTED, 6, '<', HashType::HASH_TYPE_ID, 0);
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$scope, $pf]]);
    
    $this->assertGreaterThanOrEqual(2, count($results));
    foreach ($results as $ht) {
      $this->assertLessThan(6, $ht->getIsSalted());
    }
  }

  /**
   * Verify cursor filtering for ORDER BY isSalted DESC, hashTypeId ASC.
   *
   * With a cursor on the first row of the duplicated primary value, the next rows are
   * the higher primary key within the tie and then lower primary values.
   *
   * @throws Exception
   */
  public function testFilterPaginationDescPrimaryAscTieBreaker(): void {
    $testId = uniqid();
    $firstDuplicate = $this->createDatabaseObject(
      Factory::getHashTypeFactory(),
      new HashType(null, 'ht_dup1_' . $testId, 5, 0)
    );
    $secondDuplicate = $this->createDatabaseObject(
      Factory::getHashTypeFactory(),
      new HashType(null, 'ht_dup2_' . $testId, 5, 0)
    );
    $lowerPrimary = $this->createDatabaseObject(
      Factory::getHashTypeFactory(),
      new HashType(null, 'ht_low_' . $testId, 1, 0)
    );

    $scope = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $pf = new PaginationFilter(
      HashType::IS_SALTED,
      5,
      '<',
      HashType::HASH_TYPE_ID,
      $firstDuplicate->getId(),
      null,
      '>'
    );
    $results = Factory::getHashTypeFactory()->filter([
      Factory::FILTER => [$scope, $pf],
      Factory::ORDER => [
        new OrderFilter(HashType::IS_SALTED, 'DESC'),
        new OrderFilter(HashType::HASH_TYPE_ID, 'ASC'),
      ],
    ]);

    $this->assertCount(2, $results);
    $this->assertSame($secondDuplicate->getId(), $results[0]->getId());
    $this->assertSame($lowerPrimary->getId(), $results[1]->getId());
  }
  
  /**
   * Verify that the pagination condition is combined as a whole with the other filters.
   *
   * The row in scope and the row out of scope share the primary value, so the out of scope row only matches the
   * tie-break branch of the condition. It must still be excluded by the scope filter (e.g. an ACL filter).
   *
   * @throws Exception
   */
  public function testFilterPaginationTieBreakerRespectsOtherFilters(): void {
    $testId = uniqid();
    $inScope = $this->createDatabaseObject(
      Factory::getHashTypeFactory(),
      new HashType(null, 'ht_in_' . $testId, 5, 0)
    );
    $outOfScope = $this->createDatabaseObject(
      Factory::getHashTypeFactory(),
      new HashType(null, 'ht_out_' . $testId, 5, 0)
    );
    
    $scope = new LikeFilter(HashType::DESCRIPTION, 'ht_in_' . $testId);
    $pf = new PaginationFilter(HashType::IS_SALTED, 5, '>', HashType::HASH_TYPE_ID, $inScope->getId() - 1);
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$scope, $pf]]);
    
    $ids = array_map(fn($ht) => $ht->getId(), $results);
    $this->assertContains($inScope->getId(), $ids);
    $this->assertNotContains($outOfScope->getId(), $ids);
  }
}
