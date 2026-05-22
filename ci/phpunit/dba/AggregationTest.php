<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;
use RuntimeException;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class AggregationTest extends TestBase {
  /**
   * Test all four available functions on aggregations possible
   *
   * @throws Exception
   */
  public function testAggregationSuccessAllFunctions(): void {
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testid, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testid, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testid, 72, 0));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testid);
    $aggregations = [];
    $aggregations[] = new Aggregation(HashType::IS_SALTED, Aggregation::MAX);
    $aggregations[] = new Aggregation(HashType::IS_SALTED, Aggregation::MIN);
    $aggregations[] = new Aggregation(HashType::IS_SALTED, Aggregation::SUM);
    $aggregations[] = new Aggregation(HashType::IS_SALTED, Aggregation::COUNT);
    
    $results = Factory::getHashTypeFactory()->multicolAggregationFilter([Factory::FILTER => $qF], $aggregations);
    foreach ($aggregations as $aggregation) {
      $this->assertArrayHasKey($aggregation->getName(), $results);
    }
    $this->assertEquals(125, $results[$aggregations[0]->getName()]);
    $this->assertEquals(1, $results[$aggregations[1]->getName()]);
    $this->assertEquals(198, $results[$aggregations[2]->getName()]);
    $this->assertEquals(3, $results[$aggregations[3]->getName()]);
  }
  
  /**
   * Test using different columns in one request to aggregate on.
   *
   * @throws Exception
   */
  public function testAggregationSuccessMixedColumns(): void {
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testid, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testid, 0, 5));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testid, 72, 9));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testid);
    $aggregations = [];
    $aggregations[] = new Aggregation(HashType::IS_SALTED, Aggregation::MAX);
    $aggregations[] = new Aggregation(HashType::IS_SLOW_HASH, Aggregation::MIN);
    $aggregations[] = new Aggregation(HashType::IS_SALTED, Aggregation::SUM);
    
    $results = Factory::getHashTypeFactory()->multicolAggregationFilter([Factory::FILTER => $qF], $aggregations);
    foreach ($aggregations as $aggregation) {
      $this->assertArrayHasKey($aggregation->getName(), $results);
    }
    $this->assertEquals(72, $results[$aggregations[0]->getName()]);
    $this->assertEquals(0, $results[$aggregations[1]->getName()]);
    $this->assertEquals(73, $results[$aggregations[2]->getName()]);
  }
  
  /**
   * Test an aggregation with a join.
   *
   * @throws Exception
   */
  public function testAggregationSuccessWithJoin(): void {
    $testid = uniqid();
    $hashtype1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testid, 1, 10));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testid, 0, 5));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testid, 72, 9));
    
    $accessGroup = $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'testgroup1' . $testid));
    
    $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, 'hashlist1' . $testid, 0, $hashtype1->getId(), 0, 0, 0, 0, 0, 0, $accessGroup->getId(), '', 0, 0, 0));
    
    $qF = new LikeFilter(HASHLIST::HASHLIST_NAME, "%" . $testid, Factory::getHashlistFactory());
    $jF = new JoinFilter(Factory::getHashlistFactory(), HashType::HASH_TYPE_ID, Hashlist::HASH_TYPE_ID);
    
    $aggregations = [];
    $aggregations[] = new Aggregation(HashType::HASH_TYPE_ID, Aggregation::COUNT);
    $aggregations[] = new Aggregation(HashType::IS_SLOW_HASH, Aggregation::SUM);
    
    $results = Factory::getHashTypeFactory()->multicolAggregationFilter([Factory::FILTER => $qF, Factory::JOIN => $jF], $aggregations);
    $this->assertEquals(1, $results[$aggregations[0]->getName()]);
    $this->assertEquals(10, $results[$aggregations[1]->getName()]);
  }
  
  /**
   * Test providing an invalid aggregation function
   *
   * @return void
   */
  public function testAggregationInvalidFunction(): void {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage("Invalid function for aggregation!");
    
    new Aggregation(HashType::IS_SLOW_HASH, "INVALID");
  }
  
  /**
   * Test providing an overrideFactory which does not have the column aggregating on.
   *
   * @return void
   */
  public function testAggregationInvalidColumnOverride(): void {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage("Provided column for aggregation does not match to overrideFactory!");
    
    new Aggregation(HashType::IS_SLOW_HASH, Aggregation::MAX, Factory::getFileFactory());
  }
  
  /**
   * Test providing an invalid factory with the aggregation.
   *
   * @throws Exception
   */
  public function testAggregationInvalidColumn(): void {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage("Provided column for aggregation does not match to factory!");
    
    $aggregation = new Aggregation(HashType::IS_SLOW_HASH, Aggregation::MAX);
    $aggregation->getQueryString(Factory::getFileFactory());
  }
  
  /**
   * Test include table functionality.
   */
  public function testAggregationTableInclude(): void {
    $aggregation = new Aggregation(HashType::HASH_TYPE_ID, Aggregation::COUNT);
    $query = $aggregation->getQueryString(Factory::getHashTypeFactory(), true);
    $this->assertEquals("COUNT(HashType.hashTypeId) AS count_hashtypeid", $query);
  }
  
  /**
   * Test if the mapping of a table happens when needed.
   */
  public function testAggregationTableMapping(): void {
    $aggregation = new Aggregation(User::USERNAME, Aggregation::COUNT);
    $query = $aggregation->getQueryString(Factory::getUserFactory(), true);
    $this->assertEquals("COUNT(htp_User.username) AS count_username", $query);
  }
}