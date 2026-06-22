<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class ComparisonFilterTest extends TestBase {
  /** Verify column-vs-column equality produces 'col1=col2'. */
  public function testBasicEquality(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=');
    $this->assertEquals('hashlistId=hashTypeId', $filter->getQueryString(Factory::getHashlistFactory()));
  }
  
  /** Verify table prefix is included: 'Table.col1=Table.col2'. */
  public function testWithTablePrefix(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=');
    $this->assertEquals(
      'Hashlist.hashlistId=Hashlist.hashTypeId',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify mapped table name (htp_User) is used in both column references. */
  public function testWithMappedTable(): void {
    $filter = new ComparisonFilter(User::USERNAME, User::EMAIL, '=');
    $this->assertEquals(
      'htp_User.username=htp_User.email',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /** Verify all operators (!=, <, >, <=, >=) produce the correct query string. */
  public function testDifferentOperators(): void {
    $factory = Factory::getHashlistFactory();
    $ops = ['!=', '<', '>', '<=', '>='];
    foreach ($ops as $op) {
      $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, $op);
      $this->assertEquals(
        "hashlistId{$op}hashTypeId",
        $filter->getQueryString($factory),
        "Operator $op should produce hashlistId{$op}hashTypeId"
      );
    }
  }
  
  /** Verify overrideFactory resolves columns from the override regardless of the passed factory. */
  public function testOverrideFactory(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=', Factory::getHashlistFactory());
    $this->assertEquals(
      'hashlistId=hashTypeId',
      $filter->getQueryString(Factory::getUserFactory())
    );
  }
  
  /** Verify overrideFactory with table prefix uses the override's table name. */
  public function testOverrideFactoryWithTable(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=', Factory::getHashlistFactory());
    $this->assertEquals(
      'Hashlist.hashlistId=Hashlist.hashTypeId',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /** Verify getValue returns null (comparison filters have no bound value). */
  public function testGetValueReturnsNull(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=');
    $this->assertNull($filter->getValue());
  }
  
  /** Verify getHasValue returns false (comparison filters have no bound value). */
  public function testGetHasValueReturnsFalse(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=');
    $this->assertFalse($filter->getHasValue());
  }
  
  /**
   * Create 3 hash types and filter where isSalted = isSlowHash.
   * 2 out of 3 rows should match.
   *
   * @throws Exception
   */
  public function testFilterEquality(): void {
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testid, 5, 5));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testid, 5, 10));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3' . $testid, 0, 0));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testid);
    $cF = new ComparisonFilter(HashType::IS_SALTED, HashType::IS_SLOW_HASH, '=');
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);
    
    $this->assertCount(2, $result);
    foreach ($result as $ht) {
      $this->assertEquals($ht->getIsSalted(), $ht->getIsSlowHash());
    }
  }
  
  /**
   * Create 3 hash types and filter where isSalted != isSlowHash.
   * Only 1 row (5 vs 10) should match.
   *
   * @throws Exception
   */
  public function testFilterNotEqual(): void {
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testid, 5, 5));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testid, 5, 10));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3' . $testid, 0, 0));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testid);
    $cF = new ComparisonFilter(HashType::IS_SALTED, HashType::IS_SLOW_HASH, '!=');
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);
    
    $this->assertCount(1, $result);
    foreach ($result as $ht) {
      $this->assertNotEquals($ht->getIsSalted(), $ht->getIsSlowHash());
    }
  }
  
  /**
   * Create 3 hash types and filter where isSalted > isSlowHash.
   * 2 rows (3>1, 10>0) should match.
   *
   * @throws Exception
   */
  public function testFilterGreaterThan(): void {
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testid, 3, 1));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testid, 0, 5));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3' . $testid, 10, 0));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testid);
    $cF = new ComparisonFilter(HashType::IS_SALTED, HashType::IS_SLOW_HASH, '>');
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);
    
    $this->assertCount(2, $result);
    foreach ($result as $ht) {
      $this->assertGreaterThan($ht->getIsSlowHash(), $ht->getIsSalted());
    }
  }
  
  /**
   * Create 3 hash types and filter where isSalted < isSlowHash.
   * 2 rows (1<3, 0<10) should match.
   *
   * @throws Exception
   */
  public function testFilterLessThan(): void {
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testid, 1, 3));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testid, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3' . $testid, 0, 10));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testid);
    $cF = new ComparisonFilter(HashType::IS_SALTED, HashType::IS_SLOW_HASH, '<');
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);
    
    $this->assertCount(2, $result);
    foreach ($result as $ht) {
      $this->assertLessThan($ht->getIsSlowHash(), $ht->getIsSalted());
    }
  }
  
  /**
   * Create 2 hash types where isSalted != isSlowHash and filter with '='.
   * No rows should match.
   *
   * @throws Exception
   */
  public function testFilterNoMatch(): void {
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testid, 1, 3));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testid, 5, 10));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testid);
    $cF = new ComparisonFilter(HashType::IS_SALTED, HashType::IS_SLOW_HASH, '=');
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);
    
    $this->assertCount(0, $result);
  }
  
  /**
   * Use columnFilter with ComparisonFilter (isSalted > isSlowHash).
   * Only IDs of the 2 matching rows should be returned.
   *
   * @throws Exception
   */
  public function testFilterWithColumnFilter(): void {
    $testid = uniqid();
    $ht1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testid, 50, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testid, 0, 50));
    $ht3 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3' . $testid, 50, 0));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testid);
    $cF = new ComparisonFilter(HashType::IS_SALTED, HashType::IS_SLOW_HASH, '>');
    $ids = Factory::getHashTypeFactory()->columnFilter([Factory::FILTER => [$lF, $cF]], HashType::HASH_TYPE_ID);
    
    $this->assertCount(2, $ids);
    $this->assertEqualsCanonicalizing([$ht1->getId(), $ht3->getId()], $ids);
  }
}
