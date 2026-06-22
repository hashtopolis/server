<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class ComparisonFilterTest extends TestBase {
  public function testBasicEquality(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=');
    $this->assertEquals('hashlistId=hashTypeId', $filter->getQueryString(Factory::getHashlistFactory()));
  }
  
  public function testWithTablePrefix(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=');
    $this->assertEquals(
      'Hashlist.hashlistId=Hashlist.hashTypeId',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  public function testWithMappedTable(): void {
    $filter = new ComparisonFilter(User::USERNAME, User::EMAIL, '=');
    $this->assertEquals(
      'htp_User.username=htp_User.email',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  public function testDifferentOperators(): void {
    $factory = Factory::getHashlistFactory();
    $ops = ['!=', '<', '>', '<=', '>='];
    foreach ($ops as $op) {
      $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, $op);
      $this->assertEquals(
        "hashlistId{$op}hashTypeId",
        $filter->getQueryString($factory),
        "Operator {$op} should produce hashlistId{$op}hashTypeId"
      );
    }
  }
  
  public function testOverrideFactory(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=', Factory::getHashlistFactory());
    $this->assertEquals(
      'hashlistId=hashTypeId',
      $filter->getQueryString(Factory::getUserFactory(), false)
    );
  }
  
  public function testOverrideFactoryWithTable(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=', Factory::getHashlistFactory());
    $this->assertEquals(
      'Hashlist.hashlistId=Hashlist.hashTypeId',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  public function testGetValueReturnsNull(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=');
    $this->assertNull($filter->getValue());
  }
  
  public function testGetHasValueReturnsFalse(): void {
    $filter = new ComparisonFilter(Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, '=');
    $this->assertFalse($filter->getHasValue());
  }
  
  /**
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
