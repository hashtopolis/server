<?php

namespace dba;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\Hash;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\HealthCheck;
use Hashtopolis\dba\models\HealthCheckAgent;
use Random\RandomException;
use TestBase;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\OrderFilter;
use Exception;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\User;

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
   * Test that for a aapped key the return value gets mapped
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
    $this->assertEquals(1, $hashType->getIsSalted());
    $this->assertEquals(1, $hashType->getIsSlowHash());
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
   */
  public function testColumnFilter(): void {
    // add some data
    $hashlist_1 = $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, "hashlist 1", DHashlistFormat::PLAIN, 0, 0, ':', 0, 0, 0, 0, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), "", 0, 0, 0));
    $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, "hashlist 2", DHashlistFormat::PLAIN, 0, 0, ':', 0, 0, 0, 1, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), "", 0, 0, 0));
    $hashlist_3 = $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, "hashlist 3", DHashlistFormat::PLAIN, 0, 0, ':', 0, 0, 0, 0, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), "", 0, 0, 0));
    
    $oF = new OrderFilter(Hashlist::HASHLIST_ID, "ASC");
    
    // test column filter to retrieve some of their IDs
    $qF = new QueryFilter(Hashlist::IS_SALTED, 0, "=");
    $ids = Factory::getHashlistFactory()->columnFilter([Factory::FILTER => $qF, Factory::ORDER => $oF], Hashlist::HASHLIST_ID);
    
    // hashlist 1 and 3 should be returned
    $this->assertSame([$hashlist_1->getId(), $hashlist_3->getId()], $ids);
    
    $qF = new QueryFilter(Hashlist::CRACKED, 0, ">");
    $ids = Factory::getHashlistFactory()->columnFilter([Factory::FILTER => $qF, Factory::ORDER => $oF], Hashlist::HASHLIST_ID);
    $this->assertSame([], $ids);
  }
  
  /**
   * Tests the case with no entries in a timeseries filter.
   *
   * @return void
   * @throws Exception
   */
  public function testTimeseriesFilterEmpty(): void {
    $counts = Factory::getHashFactory()->columnTimeseriesFilter([], Hash::TIME_CRACKED);
    
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
}
