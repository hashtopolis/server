<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\StartupConfig;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class PaginationFilterTest extends TestBase {
  /** Verify basic pagination query string without table prefix. */
  public function testQueryStringBasic(): void {
    $filter = new PaginationFilter(HashType::IS_SALTED, 5, '>', HashType::HASH_TYPE_ID, 0);
    $this->assertEquals(
      '(isSalted>?) OR (isSalted=? AND hashTypeId>?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify table prefix is included when includeTable=true. */
  public function testQueryStringWithTable(): void {
    $filter = new PaginationFilter(HashType::IS_SALTED, 5, '>', HashType::HASH_TYPE_ID, 0);
    $this->assertEquals(
      '(Hashlist.isSalted>?) OR (Hashlist.isSalted=? AND Hashlist.hashTypeId>?)',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify extra filters are AND-ed inside the second OR branch. */
  public function testQueryStringWithFilters(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $inner = new LikeFilter(Hashlist::HASHLIST_NAME, '%test%');
    $filter = new PaginationFilter(Hashlist::IS_SALTED, 5, '>', Hashlist::HASH_TYPE_ID, 0, [$inner]);
    $result = $filter->getQueryString(Factory::getHashlistFactory(), true);
    $this->assertStringContainsString('(Hashlist.isSalted>?)', $result);
    $this->assertStringContainsString('OR (Hashlist.isSalted=?', $result);
    $this->assertStringContainsString('AND Hashlist.hashTypeId>?', $result);
    $this->assertStringContainsString('AND Hashlist.hashlistName LIKE BINARY ?', $result);
  }
  
  /** Verify mapped table name (htp_User) is used. */
  public function testQueryStringMappedTable(): void {
    $filter = new PaginationFilter(User::USER_ID, 1, '>', User::USERNAME, '');
    $this->assertEquals(
      '(htp_User.userId>?) OR (htp_User.userId=? AND htp_User.username>?)',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /** Verify overrideFactory resolves columns from the override. */
  public function testQueryStringOverrideFactory(): void {
    $filter = new PaginationFilter(HashType::IS_SALTED, 5, '>', HashType::HASH_TYPE_ID, 0, [], Factory::getHashlistFactory());
    $this->assertEquals(
      '(Hashlist.isSalted>?) OR (Hashlist.isSalted=? AND Hashlist.hashTypeId>?)',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /** Verify getValue returns [value, value, tieBreakerValue]. */
  public function testGetValue(): void {
    $filter = new PaginationFilter(HashType::IS_SALTED, 5, '>', HashType::HASH_TYPE_ID, 10);
    $this->assertEquals([5, 5, 10], $filter->getValue());
  }
  
  /** Verify getValue includes inner filter values. */
  public function testGetValueWithFilters(): void {
    $inner = new LikeFilter(HashType::DESCRIPTION, '%search%');
    $filter = new PaginationFilter(HashType::IS_SALTED, 5, '>', HashType::HASH_TYPE_ID, 10, [$inner]);
    $this->assertEquals([5, 5, 10, '%search%'], $filter->getValue());
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
    $pf = new PaginationFilter(HashType::IS_SALTED, 3, '>', HashType::HASH_TYPE_ID, 0, [$scope]);
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
    $pf = new PaginationFilter(HashType::IS_SALTED, 6, '<', HashType::HASH_TYPE_ID, 0, [$scope]);
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$scope, $pf]]);
    
    $this->assertGreaterThanOrEqual(2, count($results));
    foreach ($results as $ht) {
      $this->assertLessThan(6, $ht->getIsSalted());
    }
  }
}
