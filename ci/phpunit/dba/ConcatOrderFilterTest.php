<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class ConcatOrderFilterTest extends TestBase {
  /** Verify ASC ordering with a single column produces 'CONCAT(col) ASC'. */
  public function testQueryStringSingleColumnAsc(): void {
    $col = new ConcatColumn(Hashlist::HASHLIST_ID, Factory::getHashlistFactory());
    $order = new ConcatOrderFilter([$col], 'ASC');
    $this->assertEquals(
      'CONCAT(hashlistId) ASC',
      $order->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify DESC ordering with a single column produces 'CONCAT(col) DESC'. */
  public function testQueryStringSingleColumnDesc(): void {
    $col = new ConcatColumn(Hashlist::HASHLIST_NAME, Factory::getHashlistFactory());
    $order = new ConcatOrderFilter([$col], 'DESC');
    $this->assertEquals(
      'CONCAT(hashlistName) DESC',
      $order->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify multiple columns produce 'CONCAT(col1, col2) ASC'. */
  public function testQueryStringMultipleColumns(): void {
    $col1 = new ConcatColumn(Hashlist::HASHLIST_ID, Factory::getHashlistFactory());
    $col2 = new ConcatColumn(Hashlist::HASHLIST_NAME, Factory::getHashlistFactory());
    $order = new ConcatOrderFilter([$col1, $col2], 'ASC');
    $this->assertEquals(
      'CONCAT(hashlistId, hashlistName) ASC',
      $order->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify column from a mapped-table factory returns 'CONCAT(col) ASC'. */
  public function testQueryStringMappedColumn(): void {
    $col = new ConcatColumn(User::USERNAME, Factory::getUserFactory());
    $order = new ConcatOrderFilter([$col], 'ASC');
    $this->assertEquals(
      'CONCAT(username) ASC',
      $order->getQueryString(Factory::getUserFactory())
    );
  }
  
  /**
   * Create 3 hash types and order them ASC by isSalted.
   * Verifies the correct sort order (1, 3, 5).
   *
   * @throws Exception
   */
  public function testOrderAsc(): void {
    $testId = uniqid();
    $ht1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'a' . $testId, 1, 0));
    $ht2 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'b' . $testId, 5, 0));
    $ht3 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'c' . $testId, 3, 0));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $col = new ConcatColumn(HashType::IS_SALTED, Factory::getHashTypeFactory());
    $oF = new ConcatOrderFilter([$col], 'ASC');
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => $lF, Factory::ORDER => $oF]);
    
    $this->assertCount(3, $result);
    $this->assertEquals($ht1->getId(), $result[0]->getId());
    $this->assertEquals($ht3->getId(), $result[1]->getId());
    $this->assertEquals($ht2->getId(), $result[2]->getId());
  }
  
  /**
   * Create 3 hash types and order them DESC by isSalted.
   * Verifies the correct sort order (5, 3, 1).
   *
   * @throws Exception
   */
  public function testOrderDesc(): void {
    $testId = uniqid();
    $ht1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'a' . $testId, 1, 0));
    $ht2 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'b' . $testId, 5, 0));
    $ht3 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'c' . $testId, 3, 0));
    
    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testId);
    $col = new ConcatColumn(HashType::IS_SALTED, Factory::getHashTypeFactory());
    $oF = new ConcatOrderFilter([$col], 'DESC');
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => $lF, Factory::ORDER => $oF]);
    
    $this->assertCount(3, $result);
    $this->assertEquals($ht2->getId(), $result[0]->getId());
    $this->assertEquals($ht3->getId(), $result[1]->getId());
    $this->assertEquals($ht1->getId(), $result[2]->getId());
  }
}
