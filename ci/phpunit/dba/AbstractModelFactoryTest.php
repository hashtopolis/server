<?php

namespace Hashtopolis\dba;

use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\Hash;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\HealthCheck;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\models\Task;
use Hashtopolis\TestBase;
use Random\RandomException;
use Hashtopolis\dba\models\Hashlist;
use Exception;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\StartupConfig;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class AbstractModelFactoryTest extends TestBase {
  /**
   * @throws Exception
   */
  public function testGetDBWithTest(): void {
    $db = Factory::getAgentFactory()->getDB(true);
    
    $this->assertNotNull($db);
  }
  
  /**
   * For non-mapped tables, the mapped table should be the same.
   *
   * @return void
   */
  public function testGetMappedModelTableWithoutMapping(): void {
    $agentFactory = Factory::getAgentFactory();
    $this->assertEquals($agentFactory->getModelTable(), $agentFactory->getMappedModelTable());
  }
  
  /**
   * For mapped tables, the mapped table should have the prefix
   *
   * @return void
   */
  public function testGetMappedModelTableWithMapping(): void {
    $userFactory = Factory::getUserFactory();
    $this->assertEquals("htp_" . $userFactory->getModelTable(), $userFactory->getMappedModelTable());
  }
  
  /**
   * Test with a normal model where no remapping is needed.
   *
   * @return void
   */
  public function testGetMappedModelKeysWithoutRemapping(): void {
    $hashType = new HashType(null, 'placeholder', 0, 0);
    $dict = $hashType->getKeyValueDict();
    $dict_mapped = AbstractModelFactory::getMappedModelKeys($hashType);
    $this->assertEquals(array_keys($dict), $dict_mapped);
  }
  
  /**
   * Test with a model where remapping is needed on a column
   *
   * @return void
   */
  public function testGetMappedModelKeysWithRemapping(): void {
    $healthCheckAgent = new HealthCheckAgent(null, 1, 1, 0, 0, 0, 0, 5, '');
    $dict = $healthCheckAgent->getKeyValueDict();
    $dict_mapped = AbstractModelFactory::getMappedModelKeys($healthCheckAgent);
    $this->assertNotEquals(array_keys($dict), $dict_mapped);
    $this->assertContains("htp_end", $dict_mapped);
  }
  
  /**
   * Test that for a non-mapped key the return value just remains the same as it was before
   *
   * @return void
   */
  public function testGetMappedModelKeyWithoutRemapping(): void {
    $hashType = new HashType(null, 'placeholder', 0, 0);
    $key_mapped = AbstractModelFactory::getMappedModelKey($hashType, HashType::IS_SALTED);
    $this->assertEquals(HashType::IS_SALTED, $key_mapped);
  }
  
  /**
   * Test that for a mapped key the return value gets mapped
   *
   * @return void
   */
  public function testGetMappedModelKeyWithRemapping(): void {
    $healthCheckAgent = new HealthCheckAgent(null, 1, 1, 0, 0, 0, 0, 5, '');
    $key_mapped = AbstractModelFactory::getMappedModelKey($healthCheckAgent, HealthCheckAgent::END);
    $this->assertEquals("htp_end", $key_mapped);
  }
  
  /**
   * Test creating a hash type object and saving it.
   *
   * @return void
   * @throws RandomException
   * @throws Exception
   */
  public function testSaveModelSuccessStaticId(): void {
    $id = 100000 + random_int(10, 999);
    $hashType = new HashType($id, 'placeholder', 0, 0);
    $hashType = Factory::getHashTypeFactory()->save($hashType);
    $this->registerDatabaseObject(Factory::getHashTypeFactory(), $hashType);
    $this->assertEquals($id, $hashType->getId());
  }
  
  /**
   * Test creating a hash type object without providing an id and let it auto increment.
   *
   * @return void
   * @throws Exception
   */
  public function testSaveModelSuccessNoId(): void {
    $hashType = new HashType(null, 'placeholder', 0, 0);
    $hashType = Factory::getHashTypeFactory()->save($hashType);
    $this->registerDatabaseObject(Factory::getHashTypeFactory(), $hashType);
    $this->assertNotEquals(null, $hashType->getId());
  }
  
  /**
   * Test just updating a model without any change, should remain the same in the database.
   *
   * @return void
   * @throws Exception
   */
  public function testUpdateModelSuccessNoChanges(): void {
    $hashType = new HashType(null, 'placeholder', 0, 0);
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), $hashType);
    $this->assertNotNull($hashType->getId());
    
    Factory::getHashTypeFactory()->update($hashType);
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType->getId());
    $this->assertEquals($hashType->getKeyValueDict(), $hashTypeUpdated->getKeyValueDict());
  }
  
  /**
   * Test updating a model with a single change.
   *
   * @return void
   * @throws Exception
   */
  public function testUpdateModelSuccessSingleChange(): void {
    $hashType = new HashType(null, 'placeholder', 0, 0);
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), $hashType);
    $this->assertNotNull($hashType->getId());
    $this->assertTrue($hashType instanceof HashType);
    
    $hashType->setDescription('HashType X');
    Factory::getHashTypeFactory()->update($hashType);
    
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType->getId());
    $this->assertEquals('HashType X', $hashTypeUpdated->getDescription());
  }
  
  /**
   * Test updating a model with a single change on a column which needs to be mapped to check mapping functionality.
   * We have to create some objects first and then be able to create a HealthCheckAgent relation where the column
   * 'end' needs to be mapped in the database as it is a reserved keyword. We check that we get the update from a
   * re-read from the database
   *
   * @return void
   * @throws Exception
   */
  public function testUpdateModelSuccessSingleChangeOnMappedColumn(): void {
    [$agent, $healthCheck] = $this->setupHealthCheck();
    
    $healthCheckAgent = new HealthCheckAgent(null, $healthCheck->getId(), $agent->getId(), 0, 0, 0, 0, 0, '');
    $healthCheckAgent = $this->createDatabaseObject(Factory::getHealthCheckAgentFactory(), $healthCheckAgent);
    $this->assertNotNull($healthCheckAgent->getId());
    $this->assertTrue($healthCheckAgent instanceof HealthCheckAgent);
    
    $healthCheckAgent->setEnd(9999);
    Factory::getHealthCheckAgentFactory()->update($healthCheckAgent);
    
    $healthCheckAgentUpdated = Factory::getHealthCheckAgentFactory()->get($healthCheckAgent->getId());
    $this->assertEquals(9999, $healthCheckAgentUpdated->getEnd());
  }
  
  /**
   * Test updating a model with multiple changed columns.
   *
   * @return void
   * @throws Exception
   */
  public function testUpdateModelSuccessMultipleChanges(): void {
    $hashType = new HashType(null, 'placeholder', 0, 0);
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), $hashType);
    $this->assertNotNull($hashType->getId());
    $this->assertTrue($hashType instanceof HashType);
    
    $hashType->setDescription('HashType X');
    $hashType->setIsSalted(1);
    $hashType->setIsSlowHash(1);
    Factory::getHashTypeFactory()->update($hashType);
    
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType->getId());
    $this->assertEquals('HashType X', $hashTypeUpdated->getDescription());
    $this->assertEquals(1, $hashTypeUpdated->getIsSalted());
    $this->assertEquals(1, $hashTypeUpdated->getIsSlowHash());
  }
  
  /**
   * Tests if values with mset() are set properly
   *
   * @return void
   * @throws Exception
   */
  public function testMsetSuccess(): void {
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 0, 0));
    $this->assertInstanceOf(HashType::class, $hashType);
    Factory::getHashTypeFactory()->mset($hashType, [HashType::IS_SALTED => 1, HashType::IS_SLOW_HASH => 1]);
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType->getId());
    $this->assertEquals(1, $hashTypeUpdated->getIsSalted());
    $this->assertEquals(1, $hashTypeUpdated->getIsSlowHash());
  }
  
  /**
   * Tests two separate mset requests on different objects and make sure that both changes survive if they are not on the same column
   *
   * @return void
   * @throws Exception
   */
  public function testMsetSuccessTwoObjects(): void {
    $hashType1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 0, 0));
    $hashType2 = Factory::getHashTypeFactory()->get($hashType1->getId());
    $this->assertTrue($hashType1 instanceof HashType);
    $this->assertTrue($hashType2 instanceof HashType);
    
    $hashType1->setDescription('something else');
    Factory::getHashTypeFactory()->mset($hashType1, [HashType::DESCRIPTION => 'something else']);
    
    $this->assertEquals('something else', $hashType1->getDescription());
    $this->assertEquals('placeholder', $hashType2->getDescription());
    
    $hashType2->setIsSalted(1);
    Factory::getHashTypeFactory()->mset($hashType2, [HashType::IS_SALTED => 1]);
    
    $this->assertEquals(0, $hashType1->getIsSalted());
    $this->assertEquals(1, $hashType2->getIsSalted());
    
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType1->getId());
    $this->assertEquals(1, $hashTypeUpdated->getIsSalted());
    $this->assertEquals('something else', $hashTypeUpdated->getDescription());
  }
  
  /**
   * Tests if values with set() are set properly
   *
   * @return void
   * @throws Exception
   */
  public function testSetSuccess(): void {
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 0, 0));
    $this->assertTrue($hashType instanceof HashType);
    Factory::getHashTypeFactory()->set($hashType, HashType::IS_SALTED, 1);
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType->getId());
    $this->assertEquals(1, $hashTypeUpdated->getIsSalted());
    $this->assertEquals(0, $hashTypeUpdated->getIsSlowHash());
  }
  
  /**
   * Tests two separate set requests on different objects and make sure that both changes survive if they are not on the same column
   *
   * @return void
   * @throws Exception
   */
  public function testSetSuccessTwoObjects(): void {
    $hashType1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 0, 0));
    $hashType2 = clone $hashType1;
    $this->assertTrue($hashType1 instanceof HashType);
    $this->assertTrue($hashType2 instanceof HashType);
    
    $hashType1->setDescription('something else');
    Factory::getHashTypeFactory()->set($hashType1, HashType::DESCRIPTION, 'something else');
    
    $this->assertEquals('something else', $hashType1->getDescription());
    $this->assertEquals('placeholder', $hashType2->getDescription());
    
    $hashType2->setIsSalted(1);
    Factory::getHashTypeFactory()->set($hashType2, HashType::IS_SALTED, 1);
    
    $this->assertEquals(0, $hashType1->getIsSalted());
    $this->assertEquals(1, $hashType2->getIsSalted());
    
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType1->getId());
    $this->assertEquals(1, $hashTypeUpdated->getIsSalted());
    $this->assertEquals('something else', $hashTypeUpdated->getDescription());
  }
  
  /**
   * Tests that set() replaces the by-ref model with a new instance of the correct concrete type
   *
   * @return void
   * @throws Exception
   */
  public function testSetRefreshesModel(): void {
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 0, 0));
    $this->assertInstanceOf(HashType::class, $hashType);
    $originalObject = $hashType;
    $hashType = Factory::getHashTypeFactory()->set($hashType, HashType::IS_SALTED, 1);
    $this->assertNotSame($originalObject, $hashType);
    $this->assertEquals(1, $hashType->getIsSalted());
    $this->assertEquals(0, $hashType->getIsSlowHash());
  }
  
  /**
   * Tests that mset() replaces the by-ref model with a new instance of the correct concrete type
   *
   * @return void
   * @throws Exception
   */
  public function testMsetRefreshesModel(): void {
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 0, 0));
    $this->assertInstanceOf(HashType::class, $hashType);
    $originalObject = $hashType;
    $hashType = Factory::getHashTypeFactory()->mset($hashType, [HashType::IS_SALTED => 1, HashType::IS_SLOW_HASH => 1]);
    $this->assertNotSame($originalObject, $hashType);
    $this->assertEquals(1, $hashType->getIsSalted());
    $this->assertEquals(1, $hashType->getIsSlowHash());
  }
  
  /**
   * Tests if values with inc() are set properly
   *
   * @return void
   * @throws Exception
   */
  public function testIncSuccess(): void {
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 1, 0));
    Factory::getHashTypeFactory()->inc($hashType, HashType::IS_SALTED);
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType->getId());
    $this->assertEquals(2, $hashTypeUpdated->getIsSalted());
  }
  
  /**
   * Tests if values with inc() are set properly when incrementing more than 1 at a step
   *
   * @return void
   * @throws Exception
   */
  public function testIncSuccessValue(): void {
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 1, 0));
    Factory::getHashTypeFactory()->inc($hashType, HashType::IS_SALTED, 5);
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType->getId());
    $this->assertEquals(6, $hashTypeUpdated->getIsSalted());
  }
  
  /**
   * Test if we increment on different instances of the same database objects, the value at the end matches all increments together
   *
   * @return void
   * @throws Exception
   */
  public function testIncSuccessTwoObjects(): void {
    $hashType1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 1, 0));
    $hashType2 = Factory::getHashTypeFactory()->get($hashType1->getId()); // retrieve an independent copy
    $this->assertTrue($hashType1 instanceof HashType);
    
    Factory::getHashTypeFactory()->inc($hashType1, HashType::IS_SALTED, 2);
    
    $this->assertEquals(3, $hashType1->getIsSalted());
    $this->assertEquals(1, $hashType2->getIsSalted());
    
    Factory::getHashTypeFactory()->inc($hashType2, HashType::IS_SALTED, 20);
    
    $this->assertEquals(23, $hashType2->getIsSalted());
    
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType1->getId());
    $this->assertEquals(23, $hashTypeUpdated->getIsSalted());
  }
  
  /**
   * Tests that inc() is not accepting negative values (should be done with dec())
   *
   * @return void
   * @throws Exception
   */
  public function testIncFailNegative(): void {
    $this->expectException(Exception::class);
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 10, 0));
    Factory::getHashTypeFactory()->inc($hashType, HashType::IS_SALTED, -5);
  }
  
  /**
   * Tests that inc() is not accepting zero value
   *
   * @return void
   * @throws Exception
   */
  public function testIncFailZero(): void {
    $this->expectException(Exception::class);
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 10, 0));
    Factory::getHashTypeFactory()->inc($hashType, HashType::IS_SALTED, 0);
  }
  
  /**
   * Tests if values with dec() are set properly
   *
   * @return void
   * @throws Exception
   */
  public function testDecSuccess(): void {
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 10, 0));
    Factory::getHashTypeFactory()->dec($hashType, HashType::IS_SALTED);
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType->getId());
    $this->assertEquals(9, $hashTypeUpdated->getIsSalted());
  }
  
  /**
   * Tests if values with dec() are set properly when decrementing more than 1 at a step
   *
   * @return void
   * @throws Exception
   */
  public function testDecSuccessValue(): void {
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 10, 0));
    Factory::getHashTypeFactory()->dec($hashType, HashType::IS_SALTED, 6);
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType->getId());
    $this->assertEquals(4, $hashTypeUpdated->getIsSalted());
  }
  
  /**
   * Test if we decrement on different instances of the same database objects, the value at the end matches all decrements together
   *
   * @return void
   * @throws Exception
   */
  public function testDecSuccessTwoObjects(): void {
    $hashType1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 50, 0));
    $hashType2 = Factory::getHashTypeFactory()->get($hashType1->getId()); // retrieve an independent copy
    $this->assertTrue($hashType1 instanceof HashType);
    
    Factory::getHashTypeFactory()->dec($hashType1, HashType::IS_SALTED, 2);
    
    $this->assertEquals(48, $hashType1->getIsSalted());
    $this->assertEquals(50, $hashType2->getIsSalted());
    
    Factory::getHashTypeFactory()->dec($hashType2, HashType::IS_SALTED, 20);
    
    $this->assertEquals(28, $hashType2->getIsSalted());
    
    $hashTypeUpdated = Factory::getHashTypeFactory()->get($hashType1->getId());
    $this->assertEquals(28, $hashTypeUpdated->getIsSalted());
  }
  
  /**
   * Tests that dec() is not accepting negative values (should be done with inc())
   *
   * @return void
   * @throws Exception
   */
  public function testDecFailNegative(): void {
    $this->expectException(Exception::class);
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 10, 0));
    Factory::getHashTypeFactory()->dec($hashType, HashType::IS_SALTED, -5);
  }
  
  /**
   * Tests that dec() is not accepting zero value
   *
   * @return void
   * @throws Exception
   */
  public function testDecFailZero(): void {
    $this->expectException(Exception::class);
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'placeholder', 10, 0));
    Factory::getHashTypeFactory()->dec($hashType, HashType::IS_SALTED, 0);
  }
  
  /**
   * Test creation of multiple objects with massSave() and check that they are existing afterwards
   *
   * @throws Exception
   */
  public function testMassSaveSuccess(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 2, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 3, 0));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $list = Factory::getHashTypeFactory()->filter([Factory::FILTER => $qF]);
    $this->assertEquals(3, count($list));
    foreach ($list as $hashType) {
      $this->assertNotNull($hashType->getId());
    }
    $this->registerDatabaseObjects(Factory::getHashTypeFactory(), $list);
  }
  
  /**
   * Test creation of multiple objects with massSave() with objects already providing primary keys
   *
   * @throws Exception
   */
  public function testMassSaveSuccessWithPKs(): void {
    $testId = uniqid();
    $idOffset = random_int(123456, 999999);
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType($idOffset + 0, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType($idOffset + 1, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType($idOffset + 2, 'hashtype3' . $testId, 72, 0));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $list = Factory::getHashTypeFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => new OrderFilter(HashType::HASH_TYPE_ID, "ASC")]);
    $this->assertEquals(3, count($list));
    foreach ($list as $hashType) {
      $this->assertNotNull($hashType->getId());
      $this->assertEquals($idOffset, $hashType->getId());
      $idOffset++;
    }
    $this->registerDatabaseObjects(Factory::getHashTypeFactory(), $list);
  }
  
  /**
   * Test that massSave() returns false if no models are given
   *
   * @return void
   * @throws Exception
   */
  public function testMassSaveFailEmpty(): void {
    $ret = Factory::getHashTypeFactory()->massSave([]);
    $this->assertFalse($ret);
  }
  
  /**
   * Test if the max operation if giving the correct max values for two different columns
   *
   * @return void
   * @throws Exception
   */
  public function testMinMaxFilterSuccessMax(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $max_1 = Factory::getHashTypeFactory()->minMaxFilter([Factory::FILTER => $qF], HashType::IS_SALTED, "MAX");
    $max_2 = Factory::getHashTypeFactory()->minMaxFilter([Factory::FILTER => $qF], HashType::IS_SLOW_HASH, "MAX");
    
    $this->assertEquals(125, $max_1);
    $this->assertEquals(0, $max_2);
  }
  
  /**
   * Test if the min operation if giving the correct min values for two different columns
   *
   * @return void
   * @throws Exception
   */
  public function testMinMaxFilterSuccessMin(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $min_1 = Factory::getHashTypeFactory()->minMaxFilter([Factory::FILTER => $qF], HashType::IS_SALTED, "MIN");
    $min_2 = Factory::getHashTypeFactory()->minMaxFilter([Factory::FILTER => $qF], HashType::IS_SLOW_HASH, "MIN");
    
    $this->assertEquals(1, $min_1);
    $this->assertEquals(0, $min_2);
  }
  
  /**
   * Test if the min operation works on a mapped column
   *
   * @return void
   * @throws Exception
   */
  public function testMinMaxFilterSuccessMappedColumn(): void {
    $min = Factory::getHealthCheckAgentFactory()->minMaxFilter([], HealthCheckAgent::END, "MIN");
    $this->assertEquals(0, $min);
  }
  
  /**
   * Test if we can retrieve the MIN and MAX of a column with one multicolumn aggregation.
   *
   * @throws Exception
   */
  public function testMulticolAggregationFilterSuccess(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $aggregations = [];
    $aggregations[] = new Aggregation(HashType::IS_SALTED, "MAX");
    $aggregations[] = new Aggregation(HashType::IS_SALTED, "MIN");
    
    $results = Factory::getHashTypeFactory()->multicolAggregationFilter([Factory::FILTER => $qF], $aggregations);
    foreach ($aggregations as $aggregation) {
      $this->assertArrayHasKey($aggregation->getName(), $results);
    }
    $this->assertEquals(125, $results[$aggregations[0]->getName()]);
    $this->assertEquals(1, $results[$aggregations[1]->getName()]);
  }
  
  /**
   * Test receiving the column of a query.
   *
   * @return void
   * @throws Exception
   */
  public function testColumnFilterSuccess(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $column = Factory::getHashTypeFactory()->columnFilter([Factory::FILTER => $qF], HashType::IS_SALTED);
    $this->assertEquals([1, 125, 72], $column);
  }
  
  /**
   * Test receiving the column of a query with an order
   *
   * @return void
   * @throws Exception
   */
  public function testColumnFilterSuccessOrdered(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $oF = new OrderFilter(HashType::IS_SALTED, "ASC");
    $column = Factory::getHashTypeFactory()->columnFilter([Factory::FILTER => $qF, Factory::ORDER => $oF], HashType::IS_SALTED);
    $this->assertEquals([1, 72, 125], $column);
    
    $oF = new OrderFilter(HashType::IS_SALTED, "DESC");
    $column = Factory::getHashTypeFactory()->columnFilter([Factory::FILTER => $qF, Factory::ORDER => $oF], HashType::IS_SALTED);
    $this->assertEquals([125, 72, 1], $column);
  }
  
  /**
   * Test querying multiple columns with the column filter
   *
   * @return void
   * @throws Exception
   */
  public function testColumnFilterSuccessMultiple(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 1));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $columns = Factory::getHashTypeFactory()->columnFilter([Factory::FILTER => $qF], [HashType::IS_SALTED, HashType::IS_SLOW_HASH]);
    $this->assertEquals([[1, 0], [125, 0], [72, 1]], $columns);
    
    $oF = new OrderFilter(HashType::IS_SALTED, "ASC");
    $columns = Factory::getHashTypeFactory()->columnFilter([Factory::FILTER => $qF, Factory::ORDER => $oF], [HashType::IS_SALTED, HashType::IS_SLOW_HASH]);
    $this->assertEquals([[1, 0], [72, 1], [125, 0]], $columns);
  }
  
  /**
   * Test receiving the column of a query on a mapped column
   *
   * @return void
   * @throws Exception
   */
  public function testColumnFilterSuccessMappedColumn(): void {
    [$agent, $healthCheck] = $this->setupHealthCheck();
    
    $this->createDatabaseObject(Factory::getHealthCheckAgentFactory(), new HealthCheckAgent(null, $healthCheck->getId(), $agent->getId(), 0, 0, 0, 0, 0, ''));
    $this->createDatabaseObject(Factory::getHealthCheckAgentFactory(), new HealthCheckAgent(null, $healthCheck->getId(), $agent->getId(), 0, 0, 0, 0, 345, ''));
    $this->createDatabaseObject(Factory::getHealthCheckAgentFactory(), new HealthCheckAgent(null, $healthCheck->getId(), $agent->getId(), 0, 0, 0, 0, 7, ''));
    
    $qF = new QueryFilter(HealthCheckAgent::AGENT_ID, $agent->getId(), "=");
    $column = Factory::getHealthCheckAgentFactory()->columnFilter([Factory::FILTER => $qF], HealthCheckAgent::END);
    $this->assertEquals([0, 345, 7], $column);
  }
  
  /**
   * Test summing up over a column
   *
   * @return void
   * @throws Exception
   */
  public function testSumFilter(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $sum = Factory::getHashTypeFactory()->sumFilter([Factory::FILTER => $qF], HashType::IS_SALTED);
    $this->assertEquals(198, $sum);
  }
  
  /**
   * Test summing up over a an empty list of objects
   *
   * @return void
   * @throws Exception
   */
  public function testSumFilterEmpty(): void {
    $qF = new QueryFilter(HashType::DESCRIPTION, "This value will not match anywhere aaaaaaaaaaaaaaaaaaaaaaaaa", "=");
    $sum = Factory::getHashTypeFactory()->sumFilter([Factory::FILTER => $qF], HashType::IS_SALTED);
    $this->assertEquals(null, $sum);
  }
  
  /**
   * Tests the case with no entries in a timeseries filter.
   *
   * @return void
   * @throws Exception
   */
  public function testTimeseriesFilterEmpty(): void {
    $qF = new QueryFilter(Hash::HASHLIST_ID, 9999999, "=");
    $counts = Factory::getHashFactory()->columnTimeseriesFilter([Factory::FILTER => $qF], Hash::TIME_CRACKED);
    
    $this->assertSame([], $counts);
  }
  
  /**
   * Tests the case with entries but none of them matching to the timeseries filter used so the counts array is empty.
   *
   * @return void
   * @throws Exception
   */
  public function testTimeseriesFilterNoneCracked(): void {
    $timeLimit = time() - 3600 * 24 * 30; // one month back
    
    $hashlist = $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, 'hashlist', DHashlistFormat::PLAIN, 0, 100, ':', 0, 0, 0, 0, 1, '', 0, 0, 0));
    $hashTemplate = new Hash(null, $hashlist->getId(), 'hash', 'salt', '', 0, null, 0, 0);
    
    for ($i = 0; $i < 1000; $i++) {
      $this->createDatabaseObject(Factory::getHashFactory(), clone $hashTemplate);
    }
    
    $qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $qF2 = new QueryFilter(Hash::TIME_CRACKED, $timeLimit, ">");
    $qF3 = new QueryFilter(Hash::HASHLIST_ID, $hashlist->getId(), "=");
    $counts = Factory::getHashFactory()->columnTimeseriesFilter([Factory::FILTER => [$qF1, $qF2, $qF3]], Hash::TIME_CRACKED);
    
    $this->assertSame([], $counts);
  }
  
  /**
   * Tests with entries existing (both matching and not matching the filters) to return the correct amount of counts
   * per day.
   *
   * @return void
   * @throws Exception
   */
  public function testTimeseriesFilter(): void {
    $timeLimit = time() - 3600 * 24 * 30; // one month back
    
    $hashlist = $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, 'hashlist', DHashlistFormat::PLAIN, 0, 100, ':', 0, 0, 0, 0, 1, '', 0, 0, 0));
    $hashTemplate = new Hash(null, $hashlist->getId(), 'hash', 'salt', 'plaintext', 0, null, 1, 0);
    
    $hashes = [];
    for ($i = 0, $j = 0; $i < 1000; $i++) {
      $hash = clone $hashTemplate;
      $hash->setTimeCracked($timeLimit + $i - 10 + $j * 3600 * 24); // 10 hashes will fall out of the tested timeseries range
      $hash->setIsCracked(($i % 10) > 0); // every tenth hash is not cracked
      $hashes[] = $this->createDatabaseObject(Factory::getHashFactory(), $hash);
      if ($i % 23 == 0) {
        $j++;
      }
    }
    
    $qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $qF2 = new QueryFilter(Hash::TIME_CRACKED, $timeLimit, ">");
    $qF3 = new QueryFilter(Hash::HASHLIST_ID, $hashlist->getId(), "=");
    $counts = Factory::getHashFactory()->columnTimeseriesFilter([Factory::FILTER => [$qF1, $qF2, $qF3]], Hash::TIME_CRACKED);
    
    // build the array on our own to compare
    $expected = [];
    foreach ($hashes as $hash) {
      $this->assertTrue($hash instanceof Hash);
      if ($hash->getisCracked() != 1) {
        continue;
      }
      $day = date('Y-m-d', $hash->getTimeCracked());
      if (!isset($expected[$day])) {
        $expected[$day] = 0;
      }
      $expected[$day]++;
    }
    
    $this->assertEquals(array_sum($expected), array_sum($counts));
    $this->assertSame($expected, $counts);
  }
  
  /**
   * Test counting matching objects with a normal filter.
   *
   * @throws Exception
   */
  public function testCountFilter(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $sum = Factory::getHashTypeFactory()->countFilter([Factory::FILTER => $qF]);
    $this->assertEquals(3, $sum);
  }
  
  /**
   * Test successfully retrieving an object.
   *
   * @throws Exception
   */
  public function testGetFromDBSuccess(): void {
    $hashtype = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . uniqid(), 1, 0));
    $this->assertTrue($hashtype instanceof HashType);
    $hashtypeCheck = Factory::getHashTypeFactory()->getFromDB($hashtype->getId());
    
    $this->assertInstanceOf(HashType::class, $hashtypeCheck);
    $this->assertEquals($hashtype->getDescription(), $hashtypeCheck->getDescription());
  }
  
  /**
   * Test retrieving an unknown ID
   *
   * @throws Exception
   */
  public function testGetFromDBInvalidID(): void {
    $result = Factory::getHashTypeFactory()->getFromDB(999999999);
    $this->assertNull($result);
  }
  
  /**
   * Test retrieving an unknown ID from a mapped table
   *
   * @throws Exception
   */
  public function testGetFromDBInvalidIDMapped(): void {
    $result = Factory::getUserFactory()->getFromDB(999999999);
    $this->assertNull($result);
  }
  
  /**
   * Test with no filtering at all, check if the correct objects are returned and the expected number.
   *
   * @return void
   * @throws Exception
   */
  public function testFilterNoFilter(): void {
    $users = Factory::getUserFactory()->filter([]);
    
    // to avoid having issues if the database is not empty, we cross check with the count filter that the same amount of objects is returned
    $count = Factory::getUserFactory()->countFilter([]);
    $this->assertEquals($count, count($users));
    
    foreach ($users as $user) {
      $this->assertTrue($user instanceof User);
    }
  }
  
  /**
   * Test retrieving some matching entries of entries in the table with a normal filter.
   *
   * @return void
   * @throws Exception
   */
  public function testFilterNormalFilter(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $qF1 = new QueryFilter(HashType::IS_SALTED, 50, ">");
    $qF2 = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $hashtypes = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    $this->assertCount(2, $hashtypes);
    foreach ($hashtypes as $hashtype) {
      $this->assertTrue($hashtype instanceof HashType);
      $this->assertTrue($hashtype->getIsSalted() > 50);
    }
  }
  
  /**
   * Test retrieving some matching entries of entries in the table with a normal filter with specific sorting.
   *
   * @return void
   * @throws Exception
   */
  public function testFilterNormalFilterWithOrderDesc(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $qF1 = new QueryFilter(HashType::IS_SALTED, 50, ">");
    $qF2 = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $oF = new OrderFilter(HashType::IS_SALTED, "DESC");
    $hashtypes = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]);
    $this->assertCount(2, $hashtypes);
    $this->assertEquals(125, $hashtypes[0]->getIsSalted());
    $this->assertEquals(72, $hashtypes[1]->getIsSalted());
  }
  
  /**
   * Test retrieving some matching entries of entries in the table with a normal filter but limit entries
   *
   * @return void
   * @throws Exception
   */
  public function testFilterNormalFilterWithLimit(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 3, 0));
    
    $qF = new QueryFilter(HashType::IS_SLOW_HASH, 0, "=");
    $lF = new LimitFilter(2);
    $hashtypes = Factory::getHashTypeFactory()->filter([Factory::FILTER => $qF, Factory::LIMIT => $lF]);
    $this->assertCount(2, $hashtypes);
    foreach ($hashtypes as $hashtype) {
      $this->assertTrue($hashtype instanceof HashType);
      $this->assertTrue($hashtype->getIsSlowHash() == 0);
    }
  }
  
  /**
   * Test retrieving some matching entries of entries but only request one single.
   *
   * @return void
   * @throws Exception
   */
  public function testFilterNormalFilterSingle(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $qF1 = new QueryFilter(HashType::IS_SLOW_HASH, 0, "=");
    $qF2 = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $oF = new OrderFilter(HashType::HASH_TYPE_ID, "ASC");
    $hashtype = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF], true);
    $this->assertTrue($hashtype instanceof HashType);
    $this->assertEquals(1, $hashtype->getIsSalted());
    $this->assertEquals('hashtype1' . $testId, $hashtype->getDescription());
  }
  
  /**
   * Test with no filtering at all, check if the correct objects are returned and the expected number.
   *
   * @return void
   * @throws Exception
   */
  public function testFilterWithJoinsNoFilter(): void {
    $jF = new JoinFilter(Factory::getAccessGroupFactory(), File::ACCESS_GROUP_ID, AccessGroup::ACCESS_GROUP_ID);
    $joined = Factory::getFileFactory()->filter([Factory::JOIN => $jF]);
    
    // to avoid having issues if the database is not empty, we cross check with the count filter that the same amount of objects is returned
    $count = Factory::getFileFactory()->countFilter([]);
    $this->assertEquals($count, count($joined[Factory::getFileFactory()->getModelName()]));
    $this->assertEquals(count($joined[Factory::getFileFactory()->getModelName()]), count($joined[Factory::getAccessGroupFactory()->getModelName()]));
    
    foreach ($joined[Factory::getFileFactory()->getModelName()] as $file) {
      $this->assertTrue($file instanceof File);
    }
    foreach ($joined[Factory::getAccessGroupFactory()->getModelName()] as $accessGroup) {
      $this->assertTrue($accessGroup instanceof AccessGroup);
    }
  }
  
  /**
   * Test retrieving some matching entries of entries in the table with a normal filter.
   *
   * @return void
   * @throws Exception
   */
  public function testFilterWithJoinsNormalFilter(): void {
    $testId = uniqid();
    
    $accessGroup1 = $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'testgroup1' . $testId));
    $accessGroup2 = $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'testgroup2' . $testId));
    $this->assertTrue($accessGroup1 instanceof AccessGroup);
    
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file1' . $testId, 1, 0, 0, $accessGroup1->getId(), 1));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file2' . $testId, 1, 0, 0, $accessGroup2->getId(), 1));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file3' . $testId, 1, 0, 0, $accessGroup1->getId(), 1));
    
    $qF = new QueryFilter(AccessGroup::GROUP_NAME, $accessGroup1->getGroupName(), "=", Factory::getAccessGroupFactory());
    $jF = new JoinFilter(Factory::getAccessGroupFactory(), File::ACCESS_GROUP_ID, AccessGroupUser::ACCESS_GROUP_ID);
    $joined = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    $this->assertCount(2, $joined[Factory::getFileFactory()->getModelName()]);
    
    $this->assertTrue($joined[Factory::getFileFactory()->getModelName()][0] instanceof File);
    $this->assertTrue($joined[Factory::getFileFactory()->getModelName()][1] instanceof File);
    
    $this->assertEquals('file1' . $testId, $joined[Factory::getFileFactory()->getModelName()][0]->getFilename());
    $this->assertEquals('file3' . $testId, $joined[Factory::getFileFactory()->getModelName()][1]->getFilename());
    
    $this->assertTrue($joined[Factory::getAccessGroupFactory()->getModelName()][0] instanceof AccessGroup);
    $this->assertTrue($joined[Factory::getAccessGroupFactory()->getModelName()][1] instanceof AccessGroup);
    
    $this->assertEquals($joined[Factory::getAccessGroupFactory()->getModelName()][0]->getId(), $joined[Factory::getAccessGroupFactory()->getModelName()][1]->getId());
  }
  
  /**
   * Test retrieving some matching entries of entries in the table with a normal filter with specific sorting.
   *
   * @return void
   * @throws Exception
   */
  public function testFilterWithJoinsNormalFilterWithOrderDesc(): void {
    $testId = uniqid();
    
    $accessGroup1 = $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'testgroup1' . $testId));
    $accessGroup2 = $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'testgroup2' . $testId));
    $this->assertTrue($accessGroup1 instanceof AccessGroup);
    
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file1' . $testId, 1, 0, 0, $accessGroup1->getId(), 1));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file2' . $testId, 2, 0, 0, $accessGroup2->getId(), 1));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file3' . $testId, 3, 0, 0, $accessGroup1->getId(), 1));
    
    $qF = new QueryFilter(AccessGroup::GROUP_NAME, $accessGroup1->getGroupName(), "=", Factory::getAccessGroupFactory());
    $jF = new JoinFilter(Factory::getAccessGroupFactory(), File::ACCESS_GROUP_ID, AccessGroupUser::ACCESS_GROUP_ID);
    $oF = new OrderFilter(File::SIZE, "DESC");
    $joined = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF, Factory::ORDER => $oF]);
    $this->assertCount(2, $joined[Factory::getFileFactory()->getModelName()]);
    
    $this->assertTrue($joined[Factory::getFileFactory()->getModelName()][0] instanceof File);
    $this->assertTrue($joined[Factory::getFileFactory()->getModelName()][1] instanceof File);
    
    $this->assertEquals('file3' . $testId, $joined[Factory::getFileFactory()->getModelName()][0]->getFilename());
    $this->assertEquals('file1' . $testId, $joined[Factory::getFileFactory()->getModelName()][1]->getFilename());
    
    $this->assertTrue($joined[Factory::getAccessGroupFactory()->getModelName()][0] instanceof AccessGroup);
    $this->assertTrue($joined[Factory::getAccessGroupFactory()->getModelName()][1] instanceof AccessGroup);
    
    $this->assertEquals($joined[Factory::getAccessGroupFactory()->getModelName()][0]->getId(), $joined[Factory::getAccessGroupFactory()->getModelName()][1]->getId());
  }
  
  /**
   * Test retrieving some matching entries of entries in the table with a normal filter but limit entries
   *
   * @return void
   * @throws Exception
   */
  public function testFilterWithJoinsNormalFilterWithLimit(): void {
    $testId = uniqid();
    
    $accessGroup1 = $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'testgroup1' . $testId));
    $accessGroup2 = $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'testgroup2' . $testId));
    $this->assertTrue($accessGroup1 instanceof AccessGroup);
    
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file1' . $testId, 1, 0, 0, $accessGroup1->getId(), 1));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file2' . $testId, 2, 0, 0, $accessGroup2->getId(), 1));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file3' . $testId, 3, 0, 0, $accessGroup1->getId(), 1));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file4' . $testId, 4, 0, 0, $accessGroup1->getId(), 1));
    
    $qF = new QueryFilter(AccessGroup::GROUP_NAME, $accessGroup1->getGroupName(), "=", Factory::getAccessGroupFactory());
    $jF = new JoinFilter(Factory::getAccessGroupFactory(), File::ACCESS_GROUP_ID, AccessGroupUser::ACCESS_GROUP_ID);
    $lF = new LimitFilter(2);
    $joined = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF, Factory::LIMIT => $lF]);
    $this->assertCount(2, $joined[Factory::getFileFactory()->getModelName()]);
    
    $this->assertTrue($joined[Factory::getFileFactory()->getModelName()][0] instanceof File);
    $this->assertTrue($joined[Factory::getFileFactory()->getModelName()][1] instanceof File);
    
    $this->assertEquals('file1' . $testId, $joined[Factory::getFileFactory()->getModelName()][0]->getFilename());
    $this->assertEquals('file3' . $testId, $joined[Factory::getFileFactory()->getModelName()][1]->getFilename());
    
    $this->assertTrue($joined[Factory::getAccessGroupFactory()->getModelName()][0] instanceof AccessGroup);
    $this->assertTrue($joined[Factory::getAccessGroupFactory()->getModelName()][1] instanceof AccessGroup);
    
    $this->assertEquals($joined[Factory::getAccessGroupFactory()->getModelName()][0]->getId(), $joined[Factory::getAccessGroupFactory()->getModelName()][1]->getId());
  }
  
  /**
   * Test creating some db objects and then massDelete them with a filter.
   *
   * @throws Exception
   */
  public function testMassDeletionSuccess(): void {
    $testId = uniqid();
    Factory::getHashTypeFactory()->save(new HashType(null, 'hashtype1' . $testId, 1, 0));
    Factory::getHashTypeFactory()->save(new HashType(null, 'hashtype2' . $testId, 125, 0));
    Factory::getHashTypeFactory()->save(new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    Factory::getHashTypeFactory()->massDeletion([Factory::FILTER => $qF]);
    
    $count = Factory::getHashTypeFactory()->countFilter([Factory::FILTER => $qF]);
    $this->assertEquals(0, $count);
  }
  
  /**
   * Test if we can update two of three entries within one query with different values.
   *
   * @throws Exception
   */
  public function testMassSingleUpdate(): void {
    $testId = uniqid();
    $hashtype1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $hashtype3 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $updates = [];
    $updates[] = new MassUpdateSet($hashtype1->getId(), 5);
    $updates[] = new MassUpdateSet($hashtype3->getId(), 9);
    
    Factory::getHashTypeFactory()->massSingleUpdate(HashType::HASH_TYPE_ID, HashType::IS_SALTED, $updates);
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $sum = Factory::getHashTypeFactory()->sumFilter([Factory::FILTER => $qF], HashType::IS_SALTED);
    $this->assertEquals(139, $sum);
  }
  
  /**
   * Test if we apply a useless update and check that it still can be executed
   *
   * @throws Exception
   */
  public function testMassSingleUpdateNoEffect(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $updates = [];
    $updates[] = new MassUpdateSet(999999, 5);
    $updates[] = new MassUpdateSet(999998, 9);
    
    Factory::getHashTypeFactory()->massSingleUpdate(HashType::HASH_TYPE_ID, HashType::IS_SALTED, $updates);
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $sum = Factory::getHashTypeFactory()->sumFilter([Factory::FILTER => $qF], HashType::IS_SALTED);
    $this->assertEquals(198, $sum);
  }
  
  /**
   * Test updating multiple objects at once and check that all got this value set.
   *
   * @throws Exception
   */
  public function testMassUpdateSuccess(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $uS = new UpdateSet(HashType::IS_SALTED, 1);
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    Factory::getHashTypeFactory()->massUpdate([Factory::UPDATE => $uS, Factory::FILTER => $qF]);
    
    $sum = Factory::getHashTypeFactory()->sumFilter([Factory::FILTER => $qF], HashType::IS_SALTED);
    $this->assertEquals(3, $sum);
  }
  
  /**
   * Test updating multiple objects at once but with a filter not matching, so it should have no effect.
   *
   * @throws Exception
   */
  public function testMassUpdateNoEffect(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype1' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype2' . $testId, 125, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'hashtype3' . $testId, 72, 0));
    
    $uS = new UpdateSet(HashType::IS_SALTED, 1);
    $qF = new LikeFilter(HashType::DESCRIPTION, "%aaaa" . $testId);
    Factory::getHashTypeFactory()->massUpdate([Factory::UPDATE => $uS, Factory::FILTER => $qF]);
    
    $qF = new LikeFilter(HashType::DESCRIPTION, "%" . $testId);
    $sum = Factory::getHashTypeFactory()->sumFilter([Factory::FILTER => $qF], HashType::IS_SALTED);
    $this->assertEquals(198, $sum);
  }
  
  /**
   * Tests both cases to be used on a simple QueryFilter with no result.
   * When single is true, null must be returned if no matching entry was found, empty array otherwise
   *
   * @return void
   */
  public function testSimpleFilter(): void {
    $qF = new QueryFilter(User::USER_ID, 99999, "=");
    
    $user = Factory::getUserFactory()->filter([Factory::FILTER => $qF], true);
    $this->assertSame(null, $user);
    
    $user = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
    $this->assertSame([], $user);
  }
  
  /**
   * Tests the columnFilter function which returns an array of values of the given column of matching rows
   *
   * @return void
   * @throws Exception
   */
  public function testColumnFilter(): void {
    $isSalted = random_int(2, 100);
    $testId = uniqid();
    
    $hashlist_1 = $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, "hashlist 1" . $testId, DHashlistFormat::PLAIN, 0, 0, ':', 0, 0, 0, $isSalted, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), "", 0, 0, 0));
    $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, "hashlist 2" . $testId, DHashlistFormat::PLAIN, 0, 0, ':', 0, 0, 0, 1, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), "", 0, 0, 0));
    $hashlist_3 = $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, "hashlist 3" . $testId, DHashlistFormat::PLAIN, 0, 0, ':', 0, 0, 0, $isSalted, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), "", 0, 0, 0));
    
    $oF = new OrderFilter(Hashlist::HASHLIST_ID, "ASC");
    
    // test column filter to retrieve some of their IDs
    $qF1 = new QueryFilter(Hashlist::IS_SALTED, $isSalted, "=");
    $qF2 = new LikeFilter(Hashlist::HASHLIST_NAME, "%" . $testId);
    $ids = Factory::getHashlistFactory()->columnFilter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF], Hashlist::HASHLIST_ID);
    
    // hashlist 1 and 3 should be returned
    $this->assertSame([$hashlist_1->getId(), $hashlist_3->getId()], $ids);
    
    $qF1 = new QueryFilter(Hashlist::CRACKED, 5000, ">");
    $qF2 = new LikeFilter(Hashlist::HASHLIST_NAME, "%" . $testId);
    $ids = Factory::getHashlistFactory()->columnFilter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF], Hashlist::HASHLIST_ID);
    $this->assertSame([], $ids);
  }
  
  /**
   * Create a HashType, save it, delete it, verify it is gone.
   *
   * @throws Exception
   */
  public function testDeleteSuccess(): void {
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'delete_test_' . uniqid(), 0, 0));
    $this->assertNotNull($hashType->getId());
    
    $result = Factory::getHashTypeFactory()->delete($hashType);
    $this->assertTrue($result);
    
    $deleted = Factory::getHashTypeFactory()->getFromDB($hashType->getId());
    $this->assertNull($deleted);
  }
  
  /**
   * Pass a valid 6-element testProperties to getDB(true, ...).
   * Without a real DB driver the connection fails and null is returned.
   *
   * @throws Exception
   */
  public function testGetDBWithTestProperties(): void {
    $ref = new \ReflectionProperty(AbstractModelFactory::class, 'dbh');
    $orig = $ref->getValue(null);
    $ref->setValue(null, null);
    
    $properties = [
      'user' => 'testuser',
      'pass' => 'testpass',
      'type' => 'mysql',
      'server' => 'localhost',
      'port' => '3306',
      'db' => 'testdb',
    ];
    $db = Factory::getHashTypeFactory()->getDB(true, $properties);
    $this->assertNull($db);
    
    $ref->setValue(null, $orig);
  }
  
  /**
   * Set an unknown DB type and call getDB(true) — should return null.
   *
   * @throws Exception
   */
  public function testGetDBUnknownTypeReturnsNull(): void {
    $ref = new \ReflectionProperty(AbstractModelFactory::class, 'dbh');
    $orig = $ref->getValue(null);
    $ref->setValue(null, null);
    
    putenv('HASHTOPOLIS_DB_TYPE=sqlite');
    StartupConfig::getInstance(true);
    $db = Factory::getHashTypeFactory()->getDB(true);
    $this->assertNull($db);
    
    $ref->setValue(null, $orig);
  }
  
  /**
   * Use columnFilter with a JOIN to retrieve filenames scoped by group.
   *
   * @throws Exception
   */
  public function testColumnFilterWithJoin(): void {
    $testId = uniqid();
    
    $accessGroup1 = $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'ag1_' . $testId));
    $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'ag2_' . $testId));
    
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file1_' . $testId, 1, 0, 0, $accessGroup1->getId(), 1));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file2_' . $testId, 2, 0, 0, $accessGroup1->getId(), 1));
    
    $jF = new JoinFilter(Factory::getAccessGroupFactory(), File::ACCESS_GROUP_ID, AccessGroup::ACCESS_GROUP_ID);
    $filenames = Factory::getFileFactory()->columnFilter(
      [Factory::JOIN => $jF], File::FILENAME
    );
    
    $this->assertCount(2, $filenames);
    foreach ($filenames as $name) {
      $this->assertStringContainsString($testId, $name);
    }
  }
  
  /**
   * multicolAggregationFilter combined with a JoinFilter.
   *
   * @throws Exception
   */
  public function testMulticolAggregationWithJoin(): void {
    $testId = uniqid();
    
    $accessGroup1 = $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'ag1_' . $testId));
    $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'ag2_' . $testId));
    
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file1_' . $testId, 10, 0, 0, $accessGroup1->getId(), 1));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file2_' . $testId, 20, 0, 0, $accessGroup1->getId(), 1));
    
    $jF = new JoinFilter(Factory::getAccessGroupFactory(), File::ACCESS_GROUP_ID, AccessGroup::ACCESS_GROUP_ID);
    $aggregations = [
      new Aggregation(File::SIZE, 'MAX'),
      new Aggregation(File::SIZE, 'MIN'),
    ];
    
    $results = Factory::getFileFactory()->multicolAggregationFilter(
      [Factory::JOIN => $jF], $aggregations
    );
    
    $this->assertEquals(20, $results[$aggregations[0]->getName()]);
    $this->assertEquals(10, $results[$aggregations[1]->getName()]);
  }
  
  /**
   * JoinFilter with overrideOwnFactory set to a non-null factory.
   * The override resolves match1 through the override's model.
   *
   * @throws Exception
   */
  public function testFilterWithJoinOverrideOwnFactory(): void {
    $testId = uniqid();
    
    $accessGroup = $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'ag_' . $testId));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file_' . $testId, 1, 0, 0, $accessGroup->getId(), 1));
    $this->assertTrue($accessGroup instanceof AccessGroup);
    
    $qF = new QueryFilter(AccessGroup::GROUP_NAME, $accessGroup->getGroupName(), '=', Factory::getAccessGroupFactory());
    $jF = new JoinFilter(
      Factory::getAccessGroupFactory(),
      AccessGroup::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      Factory::getAccessGroupFactory()
    );
    $joined = Factory::getFileFactory()->filter([Factory::JOIN => $jF, Factory::FILTER => $qF]);
    
    $fileModelName = Factory::getFileFactory()->getModelName();
    $this->assertCount(1, $joined[$fileModelName]);
    $this->assertEquals('file_' . $testId, $joined[$fileModelName][0]->getFilename());
  }
  
  /**
   * JoinFilter with query filters applied as AND in the JOIN's ON clause.
   *
   * @throws Exception
   */
  public function testFilterWithJoinQueryFilters(): void {
    $testId = uniqid();
    
    $accessGroup = $this->createDatabaseObject(Factory::getAccessGroupFactory(), new AccessGroup(null, 'ag_' . $testId));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file1_' . $testId, 1, 0, 0, $accessGroup->getId(), 1));
    $this->createDatabaseObject(Factory::getFileFactory(), new File(null, 'file2_' . $testId, 2, 0, 0, $accessGroup->getId(), 1));
    $this->assertTrue($accessGroup instanceof AccessGroup);
    
    $qF = new QueryFilter(AccessGroup::GROUP_NAME, $accessGroup->getGroupName(), '=', Factory::getAccessGroupFactory());
    $jF = new JoinFilter(
      Factory::getAccessGroupFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      null,
      JoinFilter::INNER,
      [$qF]
    );
    $joined = Factory::getFileFactory()->filter([Factory::JOIN => $jF]);
    
    $fileModelName = Factory::getFileFactory()->getModelName();
    $this->assertCount(2, $joined[$fileModelName]);
    foreach ($joined[$fileModelName] as $file) {
      $this->assertStringContainsString($testId, $file->getFilename());
    }
  }
  
  /**
   * Pass an invalid op string to minMaxFilter — it defaults to MAX.
   *
   * @throws Exception
   */
  public function testMinMaxFilterWithInvalidOp(): void {
    $result = Factory::getHealthCheckAgentFactory()->minMaxFilter([], HealthCheckAgent::END, 'INVALID');
    $this->assertEquals(0, $result ?? 0);
  }
  
  /**
   * Basic structure check: joinAggregationFilter must return an array keyed
   * by the model table (containing the requested model objects) and by each
   * aggregation name (containing the aggregation values).
   * Uses the Task/Chunk 1-n relation described in the feature request.
   *
   * @return void
   * @throws Exception
   */
  public function testJoinAggregationFilterResultStructure(): void {
    [$task] = $this->setupTaskWithChunks([10, 20, 30]);
    
    $qF = new QueryFilter(Task::TASK_ID, $task->getId(), "=");
    $jF = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $aggregations = [new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory())];
    
    $res = Factory::getTaskFactory()->joinAggregationFilter([Factory::FILTER => $qF], $jF, $aggregations);
    
    $taskTable = Factory::getTaskFactory()->getModelTable();
    $this->assertArrayHasKey($taskTable, $res);
    $this->assertArrayHasKey($aggregations[0]->getName(), $res);
    $this->assertNotEmpty($res[$taskTable]);
    foreach ($res[$taskTable] as $model) {
      $this->assertInstanceOf(Task::class, $model);
    }
  }
  
  /**
   * The main use case from the feature description: request Tasks, join with
   * the 1-n related Chunks and determine the SUM of all checkpoints of the
   * associated chunks. One task with three chunks (checkpoints 10, 20, 30)
   * must result in a single Task row with sum_checkpoint = 60.
   *
   * @return void
   * @throws Exception
   */
  public function testJoinAggregationFilterSumCheckpoints(): void {
    [$task] = $this->setupTaskWithChunks([10, 20, 30]);
    
    $qF = new QueryFilter(Task::TASK_ID, $task->getId(), "=");
    $jF = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $aggregations = [new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory())];
    
    $res = Factory::getTaskFactory()->joinAggregationFilter([Factory::FILTER => $qF], $jF, $aggregations);
    
    $taskTable = Factory::getTaskFactory()->getModelTable();
    $this->assertCount(1, $res[$taskTable]);
    $this->assertEquals($task->getId(), $res[$taskTable][0]->getId());
    $this->assertCount(1, $res[$aggregations[0]->getName()]);
    $this->assertEquals(60, $res[$aggregations[0]->getName()][0]);
  }
  
  /**
   * Verify that multiple aggregations are returned together with the model
   * objects, each aggregation result aligned with the corresponding row.
   *
   * @return void
   * @throws Exception
   */
  public function testJoinAggregationFilterMultipleAggregations(): void {
    [$task] = $this->setupTaskWithChunks([10, 20, 30]);
    
    $qF = new QueryFilter(Task::TASK_ID, $task->getId(), "=");
    $jF = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $aggregations = [
      new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory()),
      new Aggregation(Chunk::CHECKPOINT, Aggregation::MAX, Factory::getChunkFactory()),
      new Aggregation(Chunk::CHECKPOINT, Aggregation::MIN, Factory::getChunkFactory()),
      new Aggregation(Chunk::CHECKPOINT, Aggregation::COUNT, Factory::getChunkFactory()),
    ];
    
    $res = Factory::getTaskFactory()->joinAggregationFilter([Factory::FILTER => $qF], $jF, $aggregations);
    
    $taskTable = Factory::getTaskFactory()->getModelTable();
    $this->assertCount(1, $res[$taskTable]);
    $this->assertEquals(60, $res[$aggregations[0]->getName()][0]);
    $this->assertEquals(30, $res[$aggregations[1]->getName()][0]);
    $this->assertEquals(10, $res[$aggregations[2]->getName()][0]);
    $this->assertEquals(3, $res[$aggregations[3]->getName()][0]);
  }
  
  /**
   * With several tasks each having their own chunks, the aggregation must be
   * applied per task (one result row per task). Task A has chunks summing to
   * 60, Task B has chunks summing to 5.
   *
   * @return void
   * @throws Exception
   */
  public function testJoinAggregationFilterMultipleTasks(): void {
    [$taskA] = $this->setupTaskWithChunks([10, 20, 30]);
    [$taskB] = $this->setupTaskWithChunks([2, 3]);
    
    $cF = new ContainFilter(Task::TASK_ID, [$taskA->getId(), $taskB->getId()]);
    $oF = new OrderFilter(Task::TASK_ID, "ASC");
    $jF = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $aggregations = [new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory())];
    
    $res = Factory::getTaskFactory()->joinAggregationFilter([Factory::FILTER => $cF, Factory::ORDER => $oF], $jF, $aggregations);
    
    $taskTable = Factory::getTaskFactory()->getModelTable();
    $this->assertCount(2, $res[$taskTable]);
    $this->assertEquals($taskA->getId(), $res[$taskTable][0]->getId());
    $this->assertEquals($taskB->getId(), $res[$taskTable][1]->getId());
    $this->assertEquals(60, $res[$aggregations[0]->getName()][0]);
    $this->assertEquals(5, $res[$aggregations[0]->getName()][1]);
  }
  
  /**
   * A filter on the joined table (Chunk) column must restrict the rows
   * considered for the aggregation. Only chunks with checkpoint >= 20 are
   * taken into account, so the sum over [10, 20, 30] becomes 50.
   *
   * @return void
   * @throws Exception
   */
  public function testJoinAggregationFilterWithJoinTableFilter(): void {
    [$task] = $this->setupTaskWithChunks([10, 20, 30]);
    
    $qF = new QueryFilter(Chunk::CHECKPOINT, 20, ">=", Factory::getChunkFactory());
    $jF = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $aggregations = [new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory())];
    
    $res = Factory::getTaskFactory()->joinAggregationFilter([Factory::FILTER => $qF], $jF, $aggregations);
    
    $taskTable = Factory::getTaskFactory()->getModelTable();
    $this->assertCount(1, $res[$taskTable]);
    $this->assertEquals(50, $res[$aggregations[0]->getName()][0]);
  }
  
  /**
   * Order by the aggregation result descending. The task with the larger
   * checkpoint sum must come first.
   *
   * @return void
   * @throws Exception
   */
  public function testJoinAggregationFilterWithOrder(): void {
    [$taskA] = $this->setupTaskWithChunks([2, 3]);
    [$taskB] = $this->setupTaskWithChunks([10, 20, 30]);
    
    $cF = new ContainFilter(Task::TASK_ID, [$taskA->getId(), $taskB->getId()]);
    $oF = new OrderFilter(Task::TASK_ID, "DESC");
    $jF = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $aggregations = [new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory())];
    
    $res = Factory::getTaskFactory()->joinAggregationFilter([Factory::FILTER => $cF, Factory::ORDER => $oF], $jF, $aggregations);
    
    $taskTable = Factory::getTaskFactory()->getModelTable();
    $this->assertCount(2, $res[$taskTable]);
    $this->assertEquals(max($taskA->getId(), $taskB->getId()), $res[$taskTable][0]->getId());
  }
  
  /**
   * A limit must restrict the number of returned rows.
   *
   * @return void
   * @throws Exception
   */
  public function testJoinAggregationFilterWithLimit(): void {
    [$taskA] = $this->setupTaskWithChunks([1]);
    [$taskB] = $this->setupTaskWithChunks([2]);
    [$taskC] = $this->setupTaskWithChunks([3]);
    
    $cF = new ContainFilter(Task::TASK_ID, [$taskA->getId(), $taskB->getId(), $taskC->getId()]);
    $oF = new OrderFilter(Task::TASK_ID, "ASC");
    $lF = new LimitFilter(2);
    $jF = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $aggregations = [new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory())];
    
    $res = Factory::getTaskFactory()->joinAggregationFilter(
      [Factory::FILTER => $cF, Factory::ORDER => $oF, Factory::LIMIT => $lF],
      $jF,
      $aggregations
    );
    
    $taskTable = Factory::getTaskFactory()->getModelTable();
    $this->assertCount(2, $res[$taskTable]);
    $this->assertCount(2, $res[$aggregations[0]->getName()]);
  }
  
  /**
   * When no rows match the filter, the result arrays must be empty (not null),
   * but the keys must still be present.
   *
   * @return void
   * @throws Exception
   */
  public function testJoinAggregationFilterEmptyResult(): void {
    [$task] = $this->setupTaskWithChunks([10]);
    
    $qF = new QueryFilter(Task::TASK_ID, 999999999, "=");
    $jF = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $aggregations = [new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory())];
    
    $res = Factory::getTaskFactory()->joinAggregationFilter([Factory::FILTER => $qF], $jF, $aggregations);
    
    $taskTable = Factory::getTaskFactory()->getModelTable();
    $this->assertArrayHasKey($taskTable, $res);
    $this->assertArrayHasKey($aggregations[0]->getName(), $res);
    $this->assertSame([], $res[$taskTable]);
    $this->assertSame([], $res[$aggregations[0]->getName()]);
  }
  
  /**
   * A task that has no chunks at all must still be returned (with a NULL/0
   * aggregation) when a LEFT join is used, matching the 1-n semantics where the
   * "1" side may have zero related "n" entries.
   *
   * @return void
   * @throws Exception
   */
  public function testJoinAggregationFilterLeftJoinNoChunks(): void {
    [$task] = $this->setupTaskWithChunks([10, 20]);
    // second task without any chunks
    $helper = $this->createTaskHelper();
    /** @var Task $taskEmpty */
    $taskEmpty = $helper['task'];
    
    $cF = new ContainFilter(Task::TASK_ID, [$task->getId(), $taskEmpty->getId()]);
    $oF = new OrderFilter(Task::TASK_ID, "ASC");
    $jF = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID, null, JoinFilter::LEFT);
    $aggregations = [new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory())];
    
    $res = Factory::getTaskFactory()->joinAggregationFilter([Factory::FILTER => $cF, Factory::ORDER => $oF], $jF, $aggregations);
    
    $taskTable = Factory::getTaskFactory()->getModelTable();
    $this->assertCount(2, $res[$taskTable]);
    // first task (lower id) has chunks summing to 30
    $this->assertEquals(30, $res[$aggregations[0]->getName()][0]);
    // second task has no chunks, sum must be 0 (or null) — not an error
    $this->assertContains($res[$aggregations[0]->getName()][1], [0, null]);
  }
  
  /**
   * Aggregating on a column of the main factory (Task) itself, joined with the
   * 1-n table. Verifies aggregations without overrideFactory resolve against the
   * main factory.
   *
   * @return void
   * @throws Exception
   */
  public function testJoinAggregationFilterOnMainFactoryColumn(): void {
    [$task] = $this->setupTaskWithChunks([10, 20, 30]);
    
    $qF = new QueryFilter(Task::TASK_ID, $task->getId(), "=");
    $jF = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $aggregations = [new Aggregation(Task::TASK_ID, Aggregation::COUNT)];
    
    $res = Factory::getTaskFactory()->joinAggregationFilter([Factory::FILTER => $qF], $jF, $aggregations);
    
    $taskTable = Factory::getTaskFactory()->getModelTable();
    $this->assertCount(1, $res[$taskTable]);
    // COUNT of taskId over the joined rows equals the number of chunks
    $this->assertEquals(3, $res[$aggregations[0]->getName()][0]);
  }
  
  /**
   * @return array
   * @throws Exception
   */
  private function setUpHealthCheck(): array {
    $agent = new Agent(null, '', '', 0, '', '', 0, 0, 0, '', '', 0, '', null, 0, '');
    $agent = $this->createDatabaseObject(Factory::getAgentFactory(), $agent);
    
    $hashType = new HashType(null, 'placeholder', 0, 0);
    $hashType = $this->createDatabaseObject(Factory::getHashTypeFactory(), $hashType);
    
    $crackerBinaryType = new CrackerBinaryType(null, '', 0);
    $crackerBinaryType = $this->createDatabaseObject(Factory::getCrackerBinaryTypeFactory(), $crackerBinaryType);
    
    $crackerBinary = new CrackerBinary(null, $crackerBinaryType->getId(), '', '', '');
    $crackerBinary = $this->createDatabaseObject(Factory::getCrackerBinaryFactory(), $crackerBinary);
    
    $healthCheck = new HealthCheck(null, 0, 0, 0, $hashType->getId(), $crackerBinary->getId(), 0, '');
    $healthCheck = $this->createDatabaseObject(Factory::getHealthCheckFactory(), $healthCheck);
    
    return [$agent, $healthCheck];
  }
  
  /**
   * Creates a fully wired Task and attaches a chunk for every checkpoint value
   * given in $checkpoints, returning the task, the agent and the created chunks.
   * All created objects are registered for teardown cleanup.
   *
   * @param array<int> $checkpoints checkpoint values, one chunk per entry
   * @return array{0: Task, 1: Agent, 2: array<Chunk>}
   * @throws Exception
   */
  private function setupTaskWithChunks(array $checkpoints): array {
    $helper = $this->createTaskHelper();
    /** @var Task $task */
    $task = $helper['task'];
    $agent = $this->createAgent('phpunit');
    
    $chunks = [];
    foreach ($checkpoints as $cp) {
      $chunks[] = $this->createDatabaseObject(
        Factory::getChunkFactory(),
        new Chunk(null, $task->getId(), 0, 100, $agent->getId(), time(), 0, $cp, 0, 0, 0, 0)
      );
    }
    return [$task, $agent, $chunks];
  }
}
